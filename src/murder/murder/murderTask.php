<?php
/**
  * ___       _                  ___                 _                     ____
  *| __>_ _ _| |_ _ _  _ _  ___ | . \ ___  _ _  ___ | | ___  ___  ___  _ _|_  /
  *| _>| | | | | | | || '_>/ ._>| | |/ ._>| | |/ ._>| |/ . \| . \/ ._>| '_>/ / 
  *|_| `___| |_| `___||_|  \___.|___/\___.|__/ \___.|_|\___/|  _/\___.|_| /___|
  *                                                         |_|               
  *
  * → Creator: @Wolfkid20044
  * → Team: FutureDeveloperZ
  * → Link: http://github.com/FutureDeveloperZ
  *
*/

namespace murder\murder;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;

abstract class murderTask extends Task {

    protected $owner;

    public function __construct(Plugin $owner) {
     $this->owner = $owner;
    }

    final public function getOwner(): Plugin {
     return $this->owner;
    }
 }
