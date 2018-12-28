<?php

define("GPACKAGE",'mapgen');

class MapController extends controller
{
    public $map = [];
    public $mapRev = [];
    public $rows = 35;
    public $columns = 35;
    public $steps = 240;
    public $tpm = 35;
    public $fClosedSpace = 1;
    public $nClosedSpace = 2;
    public $fDirection = 0;
    public $startPos = [0,0];
    public $items = [];
    public $itemType;
    public $itemTypeN;
    public $monsters = [];
    public $monsterType;
    public $monsterTypeN;
    public $level = 1;
    public $player;

    function __construct ()
    {
        for ($i=0; $i<$this->rows; $i++) {
            for ($j=0; $j<$this->columns; $j++) {
                $this->setTile($j,$i,'#');
                $this->mapRev[$j][$i] = 0;
            }
        }
        $this->monsterType = json_decode(file_get_contents('src/'.GPACKAGE.'/monsters.json'),true);
        $this->monsterTypeN = count($this->monsterType);
        $this->itemType = json_decode(file_get_contents('src/'.GPACKAGE.'/items.json'),true);
        $this->itemTypeN = count($this->itemType);

        if(isset($_REQUEST['level'])) {
            usleep(300000);
        }
        $this->player = session::key('player');
        $this->level = session::key('level')?:1;
        $this->steps += $this->level*10;
        if($this->player == null) $this->player = [
            "hp" => 20,
            "attack" => 5,
            "armor" => 0,
            "inventory" => '[]',
            "status" => '[]'
        ];
        session::unsetKey('player');
        session::unsetKey('level');
    }

    function updateAction()
    {
        if(isset($_REQUEST['hp'])) {

            session::key('player',[
                "hp" => $_REQUEST['hp']+3,
                "attack" => $_REQUEST['attack'],
                "armor" => $_REQUEST['armor'],
                "status" => $_REQUEST['status'],
                "inventory" => $_REQUEST['inventory']
            ]);
            session::key('level',$_REQUEST['level']);
            echo '{"msg":"ok"}'; 
        }
        
    }

    function indexAction ()
    {
        view::renderFile('index.php',GPACKAGE);
    }

    function playAction ()
    {
        $this->cave ();
    }

    function cave ()
    {
        $pos = [
            rand(10,$this->columns-11),
            rand(10,$this->rows-11)
        ];
        $this->startPos = $pos;

        $direction = $this->randDirection();
        for ($step=0; $step<$this->steps; $step++) {
            if(rand(0,$this->fDirection)==0) $direction = $this->randDirection();
            $pos[0] +=$direction[0];
            $pos[1] +=$direction[1];
            if($this->inMap($pos)) {
                if($this->forCavePath($pos)) {
                    if($this->getTile($pos[0],$pos[1])=='.') $step--;
                    $this->setTile($pos[0],$pos[1],'.');
                    if(floor($step/$this->tpm)>count($this->monsters)) {
                       $this->spawnMonster($pos);
                    }
                } else {
                    if($this->getTile($pos[0],$pos[1])=='#') {
                        $pos[0] -=$direction[0];
                        $pos[1] -=$direction[1];
                    }
                    $step--;
                }
            } else {
                $step--;
                $pos[0] -=$direction[0];
                $pos[1] -=$direction[1];
            }
        }

        $this->setTilePos($this->startPos,'<');
        if($this->level<10) $this->setTilePos($pos,'>');
        $this->addItemByName($this->randPos(),"Potion of healing");
        $this->addItemByName($this->randPos(),"Attack Upgrade");
        $this->addItemByName($this->randPos(),"Armor Upgrade");
        if($this->level % 2 == 1) {
        	do{
        		$rtype = rand(0,8);
        	}while($rtype==3 || $rtype==4);        	
            $this->addItem($this->randPos(), $rtype);
        }
        view::renderFile('displayMap.php',GPACKAGE);
    }


    function setTilePos($x,$tile) {
        $this->setTile($x[0],$x[1],$tile);
    }
    function setTile($x,$y,$tile) {
        $this->map[$x][$y] = $tile;
    }
    function getTile($x,$y) {
        return $this->map[$x][$y];
    }

    function randDirection() {
        $x = rand(0,3);
        switch ($x) {
            case 0:
                return [1,0];
                break;
            case 1:
                return [0,1];
                break;
            case 2:
                return [-1,0];
                break;
            case 3:
                return [0,-1];
                break;
        }
    }

    function countTiles($start,$end,$tile) {
        $n = 0;
        for($x=$start[0]; $x<$end[0]+1; $x++) {
            for($y=$start[1]; $y<$end[1]+1; $y++) {
                if($this->getTile($x,$y)==$tile) $n++;
            }
        }
        return $n;
    }

    function inMap($point) {
        if($point[0]<1) return false;
        if($point[1]<1) return false;
        if($point[0]>($this->columns-2)) return false;
        if($point[1]>($this->rows-2)) return false;
        return true;
    }
    function forCavePath($point) {
        if($point[0]<6) if(rand(0,1)==0) return false;
        if($point[1]<6) if(rand(0,1)==0) return false;
        if($point[0]>($this->columns-8)) if(rand(0,1)==0) return false;
        if($point[1]>($this->rows-8)) if(rand(0,1)==0) return false;

        if($point[0]<4) if(rand(0,4)>0) return false;
        if($point[1]<4) if(rand(0,4)>0) return false;
        if($point[0]>($this->columns-5)) if(rand(0,4)>0) return false;
        if($point[1]>($this->rows-5)) if(rand(0,4)>0) return false;

        //if($this->countTiles([$point[0]-1,$point[1]-1], [$point[0]+1,$point[1]+1], '.') > $this->nClosedSpace)
        //    if(rand(0,$this->fClosedSpace)>0) return false;

        if($this->countTiles([$point[0]-2,$point[1]-2], [$point[0]+2,$point[1]+2], '.') > 8)
            if(rand(0,5)>0) return false;
        
        
        return true;
    }
    function randMonster($pos) {
        do{
            $type = rand(0,$this->monsterTypeN-1);
        }while(abs($this->monsterType[$type]['level'] - $this->level)>1);
        return [
            "x"=>$pos[0],"y"=>$pos[1],"type"=>$type,
            "hp"=>10,"maxhp"=>10,"turnTime"=>0
        ];
    }
    function spawnMonster($pos) {
        if(!$this->isBlocked($pos)) {
            $this->monsters[] = $this->randMonster($pos);
        }
    }
    function isBlocked($pos) {
        $x = $pos[0]; $y = $pos[1];
        if($this->getTile($x,$y)!='.') return true;
        foreach($this->monsters as $m) {
            if($m[0]==$x && $m[1]==$y) return true;
        }
        return false;
    }
    function randPos() {
        $pos=[];
        do{
            $pos[0] = rand(0,$this->columns);
            $pos[1] = rand(0,$this->rows);
        }while($this->isBlocked($pos));
        return $pos;
    }
    function addItem($pos,$type) {
        $this->items[] = [$pos[0], $pos[1], $type];
    }
    function addItemByName($pos,$name) {
        $type = 0;
        foreach($this->itemType as $i=>$it) {
            if($it['name'] == $name) $type = $i;
        }
        $this->addItem($pos, $type);
    }
    function randItem($pos) {
        //do{
            $type = rand(0,$this->itemTypeN-1);
        //}while(abs($this->monsterType[$type]['level'] - $this->level)>1);
        $this->addItem($pos, $type);
    }
}
