function unitClass (options) {
    var that = {};

    that.context = options.context;
    that.width = options.width;
    that.height = options.height;
    that.image = options.image;
    that.x = options.x;
    that.y = options.y;
    that.hp = options.hp;
    that.maxhp = options.maxhp;
    that.attack = options.attack;
    that.armor = options.armor;
    that.type = options.type;
    that.turnTime = 0;
    that.speed = 0;
    that.inventory = [];
    if(typeof options.inventory!='undefined') that.inventory = options.inventory;
    that.status = [];
    if(typeof options.status!='undefined') that.status = options.status;
    
    that.turn = function () {
        for(i=0;i<that.status.length;i++) {
            that.status[i].timeleft -= 10;
            if(that.status[i].timeleft < 0) {
                that.removeEffect(that.status[i].effect)
                that.status.splice(i,1);
            }
        }
    };

    that.render = function () {
        drawImage(that.x,that.y, that.image);
    };

    that.move = function (dx, dy) {
        document.getElementById("msgBox").innerHTML = "&nbsp;";
        if(map[that.x+dx][that.y+dy]=='#') {
            logMsg("The wall blocks your way");
            return;
        }
        mi = getMonster(that.x+dx,that.y+dy);
        if(mi > -1) {
            attack_points = Math.floor(Math.random() * that.attack)+1;
            attack_points -= Math.floor(Math.random() * monsterType[monsters[mi].type].level)
            if(attack_points<0) attack_points = 0
            monsters[mi].hp-=attack_points;
            if(monsters[mi].hp<0) monsters[mi].hp==0;
            logMsg("You hit the "+monsters[mi].typeName()+' dealing '+attack_points+' damage');
            turnPlayed = true
            return;
        }
        iti = getItem(that.x+dx,that.y+dy);
        if(iti>-1) {
            _type = items[iti][2]
            _effect = itemType[_type].effect
            _effect_time = itemType[_type].effect_time
            _name = itemType[_type].name
            logMsg("You pick up the "+_name);
            items[iti].carrier = 1
            if(_effect[0]=='+') {
                that.addEffect(_effect)
            } else {
                that.inventory.push({itemType:_type,stock:1})
            }
            updateStats()
        }
        that.x += dx;
        that.y += dy;
        if(map[that.x][that.y]=='>') {
            logMsg("Press [space] to go downstairs");
        }
        if(map[that.x][that.y]=='<') {
            logMsg("You are not thinking to go up");
        }
        turnPlayed = true
    }

    that.addEffect = function (_effect) {
        if(_effect=="heal") {
            player.hp += 10
            if(player.hp > player.maxhp) player.hp = player.maxhp
        }
        if(_effect=="+attack") {
            that.attack++
        }
        if(_effect=="+armor") {
            that.armor++
        }
        if(_effect=="speed") {
            that.speed += 5
        }
        if(_effect=="strength") {
            that.attack+=2
            that.armor+=2
        }
        updateStats()
    }
    that.removeEffect = function (_effect) {
        if(_effect=="speed") {
            that.speed -= 5
        }
        if(_effect=="strength") {
            that.attack-=2
            that.armor-=2
        }
        updateStats()
    }

    that.addStatus = function (_effect, _effect_time) {
        that.status.push({timeleft:_effect_time*100,effect:_effect})
    }

    that.monsterMove = function (dx, dy) {
        _x = that.x+dx
        _y = that.y+dy
        if(map[_x][_y]=='#') {
            return;
        }
        if(getMonster(_x,_y) > -1) {
            return;
        }
        if(player.x==_x && player.y==_y) {
            attack_points = Math.floor(Math.random() * monsterType[that.type].level)+1;
            attack_points -= Math.floor(Math.random() * player.armor)
            if(attack_points<0) attack_points = 0
            player.hp -= attack_points;
            if(player.hp<0) {
                logMsg("<span style='color:red'>The "+that.typeName()+' kills you</span>');
                document.getElementById('play-btn-container').style.display = "block"
            } else {
                logMsg("The "+that.typeName()+' hits you for '+attack_points+' damage');
            }
            return
        }
        that.x += dx;
        that.y += dy;
    }

    that.monsterPlay = function () {
        _x = that.x
        _y = that.y
        px = player.x
        py = player.y
        if(Math.abs(px-_x)<3 && Math.abs(py-_y)<3) {
            dx = spaceship(px,_x)
            if(dx == 0) dy = spaceship(py,_y); else dy=0;
            if(map[_x+dx][_y+dy]=='#') [dx,dy] = [dy,dx]
            that.monsterMove(dx, dy);
        } else if(Math.floor(Math.random() * 2) == 0) { // random movement
            that.monsterMove(Math.floor(Math.random() * 3)-1, 0);
        } else {
            that.monsterMove(0, Math.floor(Math.random() * 3)-1);
        }    
    }

    that.typeName = function () {
        return monsterType[that.type].name
    }

    that.moveDown = function () {
        if(map[that.x][that.y]!='>') return
        for (i=0; i<monsters.length; i++) if(monsters[i].hp>0) {
            logMsg("You have not done with the creatures of this level");
            return
        }
        logMsg('You go downstairs...')
        moveDown()
    }

    that.view = function () {
        mapRev[that.x][that.y] = 4;
        for(i=that.x-4; i<that.x+5; i++) {
            for(j=that.y-4; j<that.y+5; j++) {
                diffx = i-that.x;
                diffy = j-that.y;

                if(Math.abs(diffy)>Math.abs(diffx)) {
                    dx = diffx / diffy;
                    dy = Math.sign(diffy);
                } else if (diffx!=0) {
                    dx = Math.sign(diffx);
                    dy = diffy / diffx;
                } else {
                    continue;
                }
                p = 0; loop = true;
                do {
                    p++;
                    x = Math.round(p*dx)+that.x;
                    y = Math.round(p*dy)+that.y;
                    if(inMap(x,y)) {
                        dist = Math.sqrt( Math.pow(p*dx, 2) + Math.pow(p*dy, 2) )
                        mapRev[x][y] = 5;
                        if(dist>1) mapRev[x][y] = 4;
                        if(dist>2) mapRev[x][y] = 3;
                        if(dist>3) mapRev[x][y] = 2;
                        if(dist>4) loop=false;
                    } else loop=false;
                    
                    
                } while(loop && map[x][y]!='#' && p<5);
            }
        }
    }

    return that;
}

function spaceship(val1, val2) {
    if ((val1 === null || val2 === null) || (typeof val1 != typeof val2)) {
      return null;
    }
    if (typeof val1 === 'string') {
      return (val1).localeCompare(val2);
    } else {
      if (val1 > val2) {
        return 1;
      } else if (val1 < val2) {
        return -1;
      }
      return 0;
    }
  }