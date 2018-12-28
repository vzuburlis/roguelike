<?php
$play_url = 'mapgen/play';
$update_url = 'mapgen/update';
$tile_folder = gila::base_url()."src/mapgen/tile/";
?>

<head>
    <base href="<?=gila::base_url()?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"> 
    <?=view::script("lib/gila.min.js")?>
    <?=view::script("src/mapgen/unit.js")?>
    <link href="https://fonts.googleapis.com/css?family=Niconne" rel="stylesheet">
    <link href="src/mapgen/style.css" rel="stylesheet">
</head>
<!--
    Credits
    monsters: Henrique Lazarini
    player and items: DawnLike (DragonDePlatino) 
-->

<body style="background:#000">
    <div id="main">
      
      <canvas id="map" ontouch="clickOnMap(event,this)" onclick="clickOnMap(event,this)"></canvas>
      
      <div id="controls">
        <table>
        <tr>
          <td>
          <td>
          <svg class="dir-btn" viewBox="0 0 28 28" onclick="keyPress(38)">
		  <line x1="4" y1="19" x2="15" y2="8" style="stroke:#929292;stroke-width:3"></line>
		  <line x1="24" y1="19" x2="14" y2="8" style="stroke:#929292;stroke-width:3"></line>
          </svg>
          <td>
        <tr>
          <td>
          <svg class="dir-btn" viewBox="0 0 28 28" onclick="keyPress(37)">
		  <line y1="4" x1="19" y2="15" x2="8" style="stroke:#929292;stroke-width:3"></line>
		  <line y1="24" x1="19" y2="14" x2="8" style="stroke:#929292;stroke-width:3"></line>
          </svg>
          <td>
          <td>
          <svg class="dir-btn" viewBox="0 0 28 28" onclick="keyPress(39)">
		  <line y1="4" x1="9" y2="15" x2="20" style="stroke:#929292;stroke-width:3"></line>
		  <line y1="24" x1="9" y2="14" x2="20" style="stroke:#929292;stroke-width:3"></line>
          </svg>
        <tr>
        <td>
        <td>
        <svg class="dir-btn" viewBox="0 0 28 28" onclick="keyPress(40)">
		  <line x1="4" y1="9" x2="15" y2="20" style="stroke:#929292;stroke-width:3"></line>
		  <line x1="24" y1="9" x2="14" y2="20" style="stroke:#929292;stroke-width:3"></line>
          </svg>
        <td>
        </table>
      </div>
      <div></div>
      </div>
      <p id="msgBox"></p>
      <div id="statBox">
        <div>Level <?=$c->level?></div>
        <div><img src="<?=$tile_folder?>attack.png"> <span id="pAttack"><span></div>
        <div><img src="<?=$tile_folder?>armor.png"> <span id="pArmor"><span></div>
        <div id="show-u-command"><span> [u] Use Potion</span></div>
      </div>
      <div id="play-btn-container">
          <a href="<?=$play_url?>" class="play-btn">Play Again</a>
          <br><br>
          <p>Enjoyed the game? Follow me on <a target="_blank" href="https://twitter.com/zuburlis">twitter</a> and get notified for new releases and game features.</p>
      </div>

      <div id="commands">
      <img src="<?=$tile_folder?>potion.png" class="com-btn" onclick="keypressPlay(85)">
      <img src="<?=$tile_folder?>downstairs.png" class="com-btn com-down" onclick="keypressPlay(32)">
      </div>

      <div id="use-menu">
        <div id="use-menu--title">Use Item <span onclick="keypressUse(27)" style="font-family:courier new;font-size:0.8em">[x]</span></div>
        <div id="use-menu--list"></div>
      </div>
</body>


<script>
var monsterType = <?=json_encode($c->monsterType)?>;
var map = <?=json_encode($c->map)?>;
var mapRev = <?=json_encode($c->mapRev)?>;
var monsters_data = <?=json_encode($c->monsters)?>;
var monsters = [];
var items = <?=json_encode($c->items)?>;
var canvas = document.getElementById("map");
var monsterImg = [];
var itemImg = [];
var statusImg = [];
var itemType = <?=json_encode($c->itemType)?>;
var mapWidth = <?=$c->columns?>;
var mapHeight = <?=$c->rows?>;
var timeBit = 0;
renderWidth = 14;
renderHeight = 7;
var clientWidth = window.innerWidth
|| document.documentElement.clientWidth
|| document.body.clientWidth;
if(clientWidth<500) renderWidth = 10;

canvas.width = 32*renderWidth*2+32;
canvas.height = 32*renderHeight*2+32;
context = canvas.getContext("2d");
//context.scale(2,2);


pathImage = new Image();
pathImage.src = "<?=$tile_folder?>floor2.png";
wallImage = new Image();
wallImage.src = "<?=$tile_folder?>wall.png";
playerImg = new Image();
//playerImg.src = "<?=$tile_folder?>player.png";
playerImg.src = "<?=gila::base_url()?>src/mapgen/DawnLike/Commissions/Warrior.png";
upsImg = new Image();
upsImg.src = "<?=$tile_folder?>upstairs.png"; //oneway_up_1
downsImg = new Image();
downsImg.src = "<?=$tile_folder?>downstairs.png"; //oneway_down_1
//itemImg[0].src = "<?=$tile_folder?>gauze16.png";
itemImg['shortwep'] = new Image();
itemImg["shortwep"].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Items/ShortWep.png";
itemImg['armor'] = new Image();
itemImg["armor"].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Items/Armor.png";
itemImg['potion'] = new Image();
itemImg['potion'].src =  "<?=gila::base_url()?>src/mapgen/DawnLike/Items/Potion.png";
itemImg['shortwep'] = new Image();
itemImg["shortwep"].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Items/ShortWep.png";
itemImg['scroll'] = new Image();
itemImg["scroll"].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Items/Scroll.png";
statusImg['strength'] = new Image();
statusImg['strength'].src = "<?=gila::base_url()?>src/mapgen/status/strength.png";
statusImg['speed'] = new Image();
statusImg['speed'].src = "<?=gila::base_url()?>src/mapgen/status/speed.png";
statusImg['bleeding'] = new Image();
statusImg['bleeding'].src = "<?=gila::base_url()?>src/mapgen/status/bleeding.png";

for (i=0; i<monsterType.length; i++) {
    monsterImg[i] = new Image();
    monsterImg[i].src = "<?=gila::base_url()?>"+monsterType[i].image;
}
monsterImg['pest'] = new Image();
monsterImg['pest'].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Characters/Quadraped0.png";
monsterImg['pest1'] = new Image();
monsterImg['pest1'].src = "<?=gila::base_url()?>src/mapgen/DawnLike/Characters/Quadraped1.png";

for (i=0; i<monsters_data.length; i++) {
    monsters[i] =  unitClass(monsters_data[i]);
}

var timeBit = 0;

player = unitClass({
    context: canvas.getContext("2d"),
    width: 32,
    height: 32,
    image: playerImg,
    x: <?=$c->startPos[0]?>,
    y: <?=$c->startPos[1]?>,
    hp: <?=$c->player['hp']?>,
    maxhp: 20,
    attack: <?=$c->player['attack']?>,
    armor: <?=$c->player['armor']?>,
    status: <?=$c->player['status']?>,
    inventory: <?=$c->player['inventory']?>
});
window.focus();

function moveDown() {
    let fm=new FormData()
        fm.append('hp', player.hp);
        fm.append('attack', player.attack);
        fm.append('armor', player.armor);
        fm.append('inventory', JSON.stringify(player.inventory));
        fm.append('status', JSON.stringify(player.status));
        fm.append('level', <?=$c->level+1?>);
        g.ajax({
            url: '<?=gila::base_url()?><?=$update_url?>',
            data: fm,
            method: 'post',
            fn: function(){
              window.location.href = '<?=$play_url?>?level'
            }
        })
}

function moveAnimation(dx,dy) {
    if(dx==0 && dy==1) player.spritey=0
    if(dx==0 && dy==-1) player.spritey=3
    if(dx==-1 && dy==0) player.spritey=1
    if(dx==1 && dy==0) player.spritey=2
    gameScene = 'wait'
    player.spritex=1
    player.x +=dx*0.25
    player.y +=dy*0.25
    renderMap2()

    setTimeout(function(){
        player.spritex=2
        player.x +=dx*0.25
        player.y +=dy*0.25
        renderMap2()
    },40)
    setTimeout(function(){
        player.spritex=3
        player.x +=dx*0.25
        player.y +=dy*0.25
        renderMap2()
    },80)
    setTimeout(function(){
        player.spritex=0
        player.x +=dx*0.25
        player.y +=dy*0.25
        gameScene='play'
        runTurn();
        renderMap2()
    },120)
}

</script>

<?=view::script("src/mapgen/gameplay.js")?>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-130027935-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-130027935-1');
</script>

