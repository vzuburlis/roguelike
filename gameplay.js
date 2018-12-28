function updateStats() {
    document.getElementById("pAttack").innerHTML = player.attack;
    document.getElementById("pArmor").innerHTML = player.armor;
}
function renderMap2() {
    renderMap()
}
function renderMap() {
  context.clearRect(0, 0, canvas.width, canvas.height);
  for (i=0; i< mapHeight; i++) {
    for (j=0; j< mapWidth; j++) if (mapRev[j][i]>1) {
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
  

  for (i=0; i<monsters.length; i++) if(monsters[i].hp>0){
      x = monsters[i].x
      y = monsters[i].y
      if(mapRev[x][y]>1 || monsters[i].hasStatus('tracked')) {
        //drawImage(x,y, monsterImg[monsters[i].type]);
        if(timeBit == 0) {
          drawSprite(x,y, monsterImg[monsters[i].type], 0, 0);
        } else {
          drawSprite(x,y, monsterImg[monsters[i].type], 1, 0);
        }
        drawLifebar(x,y,monsters[i].hp,monsters[i].maxhp);
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
    if(x> mapWidth -1) return false;
    if(y> mapHeight -1) return false;
    return true;
}
function countTiles(sx,sy,w,h,tile) {
  n = 0
  for (i=sx; i<sx+w; i++) {
    for (j=sy; j<sy+h; j++) if(inMap(i,j)) if(map[i][j]==tile) n++
  }
  return n
}
function blockTiles(sx,sy,w,h,tile) {
  for (let i=sx; i<sx+w; i++) {
    for (let j=sy; j<sy+h; j++) if(inMap(i,j)) if(map[i][j]!=tile) return false
  }
  return true
}
function revealMap() {
  for (let i=0; i<mapHeight; i++) {
    for (let j=0; j<mapWidth; j++) if(map[j][i]!='#' || !blockTiles(j-1,i-1,3,3,'#')){
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

var turnPlayed = false;
var gameScene = "play";

document.dblclick = function(e) { 
    e.preventDefault();
}

document.onkeydown = function (e) {
    turnPlayed = false
    e = e || window.event;
    keyPress(e.keyCode);
}

function keyPress (code) {
    if (gameScene == 'play') keypressPlay(code);
    if (gameScene == 'use-menu') keypressUse(code);

    if(turnPlayed == true) {
        runTurn();
        renderMap();
    }
}


function keypressUse (code) {
    if(code==27 || code==88) {
        popup = document.getElementById("use-menu")
        popup.style.visibility = 'hidden'
        gameScene = 'play';
        return
    }
    if(code>64 && code<71) {
        i = code - 65
        if(i < player.inventory.length) {
            _type = itemType[player.inventory[i].itemType]
            player.inventory[i].stock--
            if(player.inventory[i].stock==0) player.inventory.splice(i,1);
            if(_type.effect_time>0) player.addStatus(_type.effect, _type.effect_time)
            if(_type.sprite[0]=='potion') {
	            logMsg("You drink the " + _type.name);
	            player.addEffect(_type.effect)
            } else if(_type.sprite[0]=='scroll'){
	            logMsg("You read the " + _type.name);
	            player.spellEffect(_type.effect)
            }
            updateStats()
            popup = document.getElementById("use-menu")
            popup.style.visibility = 'hidden'
            gameScene = 'play';
            turnPlayed = true;
        }
    }
}

function keypressPlay (code) {
    if(player.hp<0) return
    if(gameScene != 'play') return

    if (code == '38') {
        // up arrow
        player.move(0,-1);
        //turnPlayed = true;
    }
    else if (code == '40') {
        // down arrow
        player.move(0,1);
        //turnPlayed = true;
    }
    else if (code == '37') {
       // left arrow
       player.move(-1,0);
       //turnPlayed = true;
    }
    else if (code == '39') {
       // right arrow
       player.move(1,0);
       //turnPlayed = true;
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
           list.innerHTML += '<div onclick="keypressUse('+(65+i)+')">&#'+(i+97)+'; <div class="item-img" style="background: url(\''+src+'\') -'+sx+' -'+sy+';"></div> <span class="item-name">'+_type.name+'</span></div><br>'
       }
       popup.style.visibility = "visible"
       gameScene = "use-menu"
       player.moveDown();
    }

}

function clickOnMap(e, canva) {
	x = e.clientX - canva.offsetLeft; 
	y = e.clientY - canva.offsetTop;
	targetx = player.x+Math.round(x/32);
	targety = player.y+Math.round(y/32);
	if(typeof playerWalk!='undefined') clearInterval(playerWalk)
	
	playerWalk = setInterval(function(){
		dx=spaceship(targetx, player.x)
		dy=spaceship(targety, player.y)
		if(Math.abs(dy)>Math.abs(dx)) {
    		if(dy==0 || player.move(0,dy)==false) if(player.move(dx,0)==false) {
		    	clearInterval(playerWalk)
	    	}
		} else {
    		if(dx==0 || player.move(dx,0)==false) if(player.move(0,dy)==false) {
		    	clearInterval(playerWalk)
	    	}
		}
        runTurn();
	    renderMap();
		if(visibleMonsters().length>0 || (player.x==targetx && player.y==targety)) {
		console.log(visibleMonsters())
			clearInterval(playerWalk)
		}
	},100)
}

function visibleMonsters() {
	vm = Array()
	for (let i=0; i<monsters.length; i++) if(monsters[i].hp>0){
		x = monsters[i].x
		y = monsters[i].y
		if(mapRev[x][y]>1 || monsters[i].hasStatus('tracked')) vm.push(i);
	}
	return vm
}

function runTurn() {
    do{
        for(let i=0;i<monsters.length;i++) if(monsters[i].hp>0) {
            monsters[i].turnTime += 10
            monsters[i].turn()
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

setInterval(function(){
    if(timeBit == 0) timeBit = 1; else timeBit = 0;
    renderMap();
}, 500);

updateStats();
