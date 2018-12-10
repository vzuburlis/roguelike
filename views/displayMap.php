<?php
$play_url = 'mapgen/play';
$update_url = 'mapgen/update';
$tile_folder = gila::config('base')."src/mapgen/tile/";
?>
<style>
body{
    font-family: courier new;
    text-align: center;
    color: white;
}

#controls {
    display:none;
    overflow: visible;
    position:absolute;
    left:1em;
    bottom:1em;
}
#msgBox, #statBox {
    position:absolute;
    text-align: center;
    right:0;
    left:0;
}
#msgBox{ top:0;}
#statBox { bottom:0; display:grid; grid-template-columns:1fr 1fr 1fr 1fr;font-size:24px}
#statBox img { width: 32px; height:32px;}
#statBox span { padding: 4px; }
#use-menu {
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    background:rgba(0,0,0,0.7);
    visibility:hidden;
    border: 2px solid #613214;
    padding: 8px;
}
button{
    font-size: 3em;
}
#play-btn-container{
    display:none;
    position: absolute;
    top:55%;
    left:0;
    right:0;
    text-align:center;
}
.play-btn {
    text-transform: uppercase;
    padding:1em 2em;
    font-size:1.5em;
    font-weight:bold;
    border-radius:0.5em;
    border: 2px solid orange;
    color: orange;
    text-decoration: none;
    margin-bottom:2em;
}
.play-btn:hover {
    color: white;
    background: orange;
}
#use-menu--title{
    font-family: 'Niconne', cursive;
    font-size: 4em;
    text-align:center;
    min-width:300px;
}
#use-menu--list{
    text-align:left;
}
#use-menu .item-img{
    width:16px;
    height:16px;
    display:inline-block;
}

</style>
<head>
    <base href="<?=gila::config('base')?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=view::script("lib/gila.min.js")?>
    <?=view::script("src/mapgen/unit.js")?>
    <link href="https://fonts.googleapis.com/css?family=Niconne" rel="stylesheet">
</head>
<!--
    Credits
    monsters: Henrique Lazarini
    player and items: DawnLike (DragonDePlatino) 
-->

<body style="background:#000">
    <div id="main">
      
      <canvas id="map"></canvas>
      
      <div id="controls">
        <table>
        <tr><td><td><button onclick="player.move(0,-1);renderMap();">U</button><td>
        <tr><td><button onclick="player.move(-1,0);renderMap();">L</button><td><td><button onclick="player.move(1,0);renderMap();">R</button>
        <tr><td><td><button onclick="player.move(0,1);renderMap();">D</button><td>
        </table>
      </div>
      <div></div>
      </div>
      <p id="msgBox"></p>
      <div id="statBox">
        <div>Level <?=$c->level?></div>
        <div><img src="<?=$tile_folder?>attack.png"> <span id="pAttack"><span></div>
        <div><img src="<?=$tile_folder?>armor.png"> <span id="pArmor"><span></div>
        <div><!--img src="<?=$tile_folder?>potion.png"> x<span id="pPotions"--><span> [u] Use Potion</div>
      </div>
      <div id="play-btn-container">
          <a href="<?=$play_url?>" class="play-btn">Play Again</a>
          <br><br>
          <p>Enjoyed the game? Follow me on <a target="_blank" href="https://twitter.com/zuburlis">twitter</a> and get notified for new releases and game features.</p>
      </div>

      <div id="use-menu">
        <div id="use-menu--title">Use Item</div>
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
renderWidth = 14;
renderHeight = 7;
canvas.width = 32*renderWidth*2+32;
canvas.height = 32*renderHeight*2+32;
context = canvas.getContext("2d");
//context.scale(2,2);


pathImage = new Image();
pathImage.src = "<?=$tile_folder?>floor2.png";
wallImage = new Image();
wallImage.src = "<?=$tile_folder?>wall.png";
playerImg = new Image();
playerImg.src = "<?=$tile_folder?>player.png";
upsImg = new Image();
upsImg.src = "<?=$tile_folder?>upstairs.png"; //oneway_up_1
downsImg = new Image();
downsImg.src = "<?=$tile_folder?>downstairs.png"; //oneway_down_1
//itemImg[0].src = "<?=$tile_folder?>gauze16.png";
itemImg['shortwep'] = new Image();
itemImg["shortwep"].src = "<?=gila::config('base')?>src/mapgen/DawnLike/Items/ShortWep.png";
itemImg['armor'] = new Image();
itemImg["armor"].src = "<?=gila::config('base')?>src/mapgen/DawnLike/Items/Armor.png";
itemImg['potion'] = new Image();
itemImg['potion'].src =  "<?=gila::config('base')?>src/mapgen/DawnLike/Items/Potion.png";
itemImg['shortwep'] = new Image();
itemImg["shortwep"].src = "<?=gila::config('base')?>src/mapgen/DawnLike/Items/ShortWep.png";
statusImg['strength'] = new Image();
statusImg['strength'].src = "<?=gila::config('base')?>src/mapgen/status/strength.png";
statusImg['speed'] = new Image();
statusImg['speed'].src = "<?=gila::config('base')?>src/mapgen/status/speed.png";
statusImg['bleeding'] = new Image();
statusImg['bleeding'].src = "<?=gila::config('base')?>src/mapgen/status/bleeding.png";

for (i=0; i<monsterType.length; i++) {
    monsterImg[i] = new Image();
    monsterImg[i].src = "<?=gila::config('base')?>"+monsterType[i].image;
}

for (i=0; i<monsters_data.length; i++) {
    monsters[i] =  unitClass(monsters_data[i]);
}

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
updateStats()
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
            url: '<?=gila::config('base')?><?=$update_url?>',
            data: fm,
            method: 'post',
            fn: function(){
              window.location.href = '<?=$play_url?>?level'
            }
        })
}

function updateStats() {
    document.getElementById("pAttack").innerHTML = player.attack;
    document.getElementById("pArmor").innerHTML = player.armor;
}
function renderMap() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  for (i=0; i< <?=$c->rows?>; i++) {
    for (j=0; j< <?=$c->columns?>; j++) if (mapRev[j][i]>1) {
        mapRev[j][i] = 1;
    }
  }
  player.view();
  for (i=player.y-renderHeight; i<=player.y+renderHeight; i++) {
    for (j=player.x-renderWidth; j<=player.x+renderWidth; j++) {
        if (inMap(j,i)) if (mapRev[j][i]>0) {
            context.globalAlpha = 0.4;
            if (mapRev[j][i] == 5) context.globalAlpha = 1;
            if (mapRev[j][i] == 4) context.globalAlpha = 0.9;
            if (mapRev[j][i] == 3) context.globalAlpha = 0.8;
            if (mapRev[j][i] == 2) context.globalAlpha = 0.6;
            if (map[j][i]=='#') drawImage(j,i, wallImage);
            if (map[j][i]=='.') drawImage(j,i, pathImage);
            if (map[j][i]=='<') { drawImage(j,i, wallImage);drawImage(j,i, upsImg); }
            if (map[j][i]=='>') { drawImage(j,i, downsImg); }
        }
    }
  }
  context.globalAlpha = 1;

  for (i=0; i<monsters.length; i++) if(monsters[i].hp>0){
      x = monsters[i].x
      y = monsters[i].y
      if(mapRev[x][y]>1) {
          drawImage(x,y, monsterImg[monsters[i].type]);
          drawLifebar(x,y,monsters[i].hp,monsters[i].maxhp);
      } 
  }

  for (i=0; i<items.length; i++) if(items[i].carrier==null) {
      x = items[i][0]
      y = items[i][1]
      if(mapRev[x][y]>1) {
          if(typeof itemType[items[i][2]]!='undefined') {
              _t = itemType[items[i][2]].sprite
            drawSprite(x,y, itemImg[_t[0]], _t[1], _t[2]);
          } else drawImage(x,y, itemImg[items[i][2]]);
      } 
  }

  player.render();
  drawLifebar(player.x,player.y,player.hp,player.maxhp);
  drawStatus(player);
}

function drawStatus(unit) {
    x = (unit.x-player.x+renderWidth)*32+16-unit.status.length*8
    y = (unit.y-player.y+renderHeight)*32-16
    for(i=0; i<unit.status.length; i++) {
        context.drawImage(
           statusImg[unit.status[i].effect],
           0, 0,
           32, 32,
           x + i*16, y,
           16, 16);
    }
}

function drawLifebar(x,y,hp,maxhp) {
    if(hp==maxhp) return
    x = x-player.x+renderWidth
    y = y-player.y+renderHeight+1
    context.fillStyle="#FF0000";
    context.fillRect(x * 32, y * 32-3, 32, 3); 
    context.fillStyle="#00FF00";
    context.fillRect(x * 32, y * 32-3, 32*hp/maxhp, 3); 
}
function drawImage (x,y,image) {
    x = x-player.x+renderWidth
    y = y-player.y+renderHeight
    context.drawImage(
           image,
           0, 0,
           16, 16,
           x * 32, y * 32,
           32, 32);
};
function drawItem (x,y,image) {
    x = x-player.x+renderWidth
    y = y-player.y+renderHeight
    context.drawImage(
           image,
           0, 0,
           16, 16,
           x * 32+12, y * 32+8,
           16, 16);
};
function drawSprite (x,y,image,sx,sy) {
    x = x-player.x+renderWidth
    y = y-player.y+renderHeight
    context.drawImage(
           image,
           sx*16, sy*16,
           16, 16,
           x * 32, y * 32,
           32, 32);
};

function inMap (x,y) {
    if(x<0) return false;
    if(y<0) return false;
    if(x> <?=$c->columns?>-1) return false;
    if(y> <?=$c->rows?>-1) return false;
    return true;
}

function revealMap() {
  for (i=0; i< <?=$c->rows?>; i++) {
    for (j=0; j< <?=$c->columns?>; j++) {
        mapRev[j][i] = 1;
    }
  }
}

function getMonster(x,y) {
    for (i=0; i<monsters.length; i++) if(monsters[i].hp>0) {
      if (x == monsters[i].x && y == monsters[i].y) return i;
    }
    return -1;
}
function getItem(x,y) {
    for (i=0; i<items.length; i++) if(items[i].carrier==null) {
      if (x == items[i][0] && y == items[i][1]) return i;
    }
    return -1;
}

function logMsg(msg) {
    document.getElementById("msgBox").innerHTML += msg+'<br>';
}

function status(params) {
    var that = {};
    that.timeleft = 1000
    that.effect = params.effect
}


function monsterMove (mi, dx, dy) {
    monsters[i].monsterMove(dx, dy)
}

var turnPlayed = false
var gameScene = "play"

document.onkeydown = function (e) {
    turnPlayed = false
    e = e || window.event;

    if (gameScene == 'play') {
        // up arrow
        keypressPlay(e.keyCode);
    }
    else if (gameScene == 'use-menu') {
        // down arrow
        keypressUse(e.keyCode);
    }

    if(turnPlayed == true) {
        runTurn();
        if(player.status.length>0) logMsg("player is "+player.status[0].effect+" "+player.status[0].timeleft)
        renderMap();
    }
}

function keypressUse (code) {
    if(code==27) {
        popup = document.getElementById("use-menu")
        popup.style.visibility = 'hidden'
        gameScene = 'play'
        return
    }
    if(code>64 && code<71) {
        i = code - 65
        if(i < player.inventory.length) {
            _type = itemType[player.inventory[i].itemType]
            logMsg("You drink the " + _type.name);
            player.inventory[i].stock--
            if(player.inventory[i].stock==0) player.inventory.splice(i,1);
            if(_type.effect_time>0) player.addStatus(_type.effect, _type.effect_time)
            player.addEffect(_type.effect)
            updateStats()
            popup = document.getElementById("use-menu")
            popup.style.visibility = 'hidden'
            gameScene = 'play'
            turnPlayed = true
        }
    }
}

function keypressPlay (code) {
    if(player.hp<0) return

    if (code == '38') {
        // up arrow
        player.move(0,-1);
    }
    else if (code == '40') {
        // down arrow
        player.move(0,1);
    }
    else if (code == '37') {
       // left arrow
       player.move(-1,0);
    }
    else if (code == '39') {
       // right arrow
       player.move(1,0);
    }
    else if (code == '32') {
       // right arrow
       player.moveDown();
    }
    else if (code == '85') { //u
       // i 73
       popup = document.getElementById("use-menu")
       list = document.getElementById("use-menu--list")
       list.innerHTML = ""
       for(i=0; i<player.inventory.length; i++) {
           _type = itemType[player.inventory[i].itemType]
           src = itemImg[_type.sprite[0]].src
           sx = _type.sprite[1]*16+'px'
           sy = _type.sprite[2]*16+'px'
           list.innerHTML += '&#'+(i+97)+'; <div class="item-img" style="background: url(\''+src+'\') -'+sx+' -'+sy+'"></div> '+_type.name+'<br>'
       }
       popup.style.visibility = "visible"
       gameScene = "use-menu"
       player.moveDown();
    }

}

function runTurn() {
    do{
        for(i=0;i<monsters.length;i++) if(monsters[i].hp>0) {
            monsters[i].turnTime += 10
            if(monsters[i].turnTime > 99) {
                monsters[i].turnTime -= 100
                monsters[i].monsterPlay()
            }
        }
        player.turnTime += 10+player.speed
        player.turn()
    }while(player.turnTime < 100)
    player.turnTime -= 100
}


setTimeout(function(){
    player.view();
    renderMap();
}, 300);

</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-130027935-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-130027935-1');
</script>

