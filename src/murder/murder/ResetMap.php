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

use murder\murder\GameSender;

Class ResetMap
{
    public function __construct(GameSender $plugin){
        $this->plugin = $plugin;
    }
    
    public function reload($lev)
    {
            $name = $lev->getFolderName();
            if ($this->plugin->getOwner()->getServer()->isLevelLoaded($name))
            {
                    $this->plugin->getOwner()->getServer()->unloadLevel($this->plugin->getOwner()->getServer()->getLevelByName($name));
            }
            $zip = new \ZipArchive;
            $zip->open($this->plugin->getOwner()->getDataFolder() . 'arenas/' . $name . '.zip');
            $zip->extractTo($this->plugin->getOwner()->getServer()->getDataPath() . 'worlds');
            $zip->close();
            unset($zip);
            $this->plugin->getOwner()->getServer()->loadLevel($name);
            return true;
    }
}