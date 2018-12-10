<?php
$play_url = 'mapgen/play';
?>
<style>
body{
    font-family: courier new;
    text-align: center;
    color: white;
    background: url(<?=gila::config('base')?>src/<?=GPACKAGE?>/images/bg.png) no-repeat center center fixed;
    background-color: black;
    background-size: cover;
}

#game-title{
    font-family: 'Niconne', cursive;
    font-size: 4em;
    padding: 80px 0;
}
#about-game{
    padding: 4em 0;
}

#main {
    padding: 10px;
    background: rgba(0,0,0,0.5);
}
#msgBox{ top:0;}
#statBox { bottom:0; display:grid; grid-template-columns:1fr 1fr 1fr 1fr;font-size:24px}
#statBox img { width: 32px; height:32px;}
#statBox span { padding: 4px; }
.play-btn {
    text-transform: uppercase;
    padding:1em 2em;
    font-size:1.5em;
    font-weight:bold;
    border-radius:0.5em;
    border: 2px solid orange;
    color: orange;
}
.play-btn:hover {
    color: white;
    background: orange;
}
button{
    font-size: 3em;
}
</style>
<head>
    <base href="<?=gila::config('base')?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=view::css("lib/gila.min.css")?>
    <?=view::script("lib/gila.min.js")?>
    <link href="https://fonts.googleapis.com/css?family=Niconne" rel="stylesheet">
</head>
<!--
    Credits
    monsters: Henrique Lazarini
    player and items: DawnLike (DragonDePlatino) 
-->

<body>
    <div id="main">
      <div id="game-title">Cave of Cenbeald</div>
      <a href="<?=$play_url?>" class="play-btn">Play</a>
      <div id="about-game">
        <p>Cenbeald the farmer summoned you to help clean the caves under his land from the filthy creatures that terrify his people.</p>
        <p>You are an unexpirienced warrior that do not affraid to fight the unknown.</p>
        <h3>Quest</h3>
        <p>Cave of Cenbeald is a coffe break roguelike. Your mission is to clear all level until level 10. Level maps are generated randomly and You have to slay all creatures in any level before you continue to the next.</p>
        <h3>Instructions</h3>
        <strong>[Directional Keys]</strong> Move one tile. If step on a monster your character hits it.<br>
        <strong>[space]</strong> Go to next level from downstairs<br>
        <strong>[u]</strong> Use an item<br>
        <h3>Credits</h3>
        Monster tiles: Henrique Lazarini<br>
        Hero and item tiles: DawnLike (DragonDePlatino)<br>
        <p>
          If you enjoy this game, follow me on <a target="_blank" href="https://twitter.com/zuburlis">twitter</a> to get notified for new releases of roguelikes.
        </p>

      </div>
      <a href="<?=$play_url?>" class="play-btn">Play</a>
      <br>
      <br>
      <br>
      <br>
      <div style="display:none">
        <img src="<?=$tile_folder?>floor2.png">
        <img src="<?=$tile_folder?>wall.png">
        <img src="<?=$tile_folder?>player.png">
        <img src="<?=$tile_folder?>upstairs.png">
        <img src="<?=$tile_folder?>downstairs.png">
        <img src="<?=gila::config('base')?>src/mapgen/DawnLike/Items/ShortWep.png">
        <img src="<?=gila::config('base')?>src/mapgen/DawnLike/Items/Potion.png">
        <img src="<?=gila::config('base')?>src/mapgen/DawnLike/Items/Armor.png">
      </div>
    </div>
</body>

