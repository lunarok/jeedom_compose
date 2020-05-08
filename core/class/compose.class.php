<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class compose extends eqLogic {
    public function loadCmdFromConf($type) {
  		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
  			return;
  		}
  		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
  		if (!is_json($content)) {
  			return;
  		}
  		$device = json_decode($content, true);
  		if (!is_array($device) || !isset($device['commands'])) {
  			return true;
  		}
  		foreach ($device['commands'] as $command) {
  			$cmd = null;
  			foreach ($this->getCmd() as $liste_cmd) {
  				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
  				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
  					$cmd = $liste_cmd;
  					break;
  				}
  			}
  			if ($cmd == null || !is_object($cmd)) {
  				$cmd = new ghlocalCmd();
  				$cmd->setEqLogic_id($this->getId());
  				utils::a2o($cmd, $command);
  				$cmd->save();
  			}
  		}
  	}

    public function preRemove() {
      if ($this->getConfiguration('type') == 'file') {
        //rm file
        //rm components
      }
      if ($this->getConfiguration('type') == 'docker') {
        $eqlogic = compose::byId($this->getConfiguration('file'));
        $eqlogic->generateComposeConf();
      }
    }

    public function postSave() {
      $this->loadCmdFromConf($this->getConfiguration('type'));
      if ($this->getConfiguration('type') == 'file') {
        //
      }
      if ($this->getConfiguration('type') == 'docker') {
        if ($this->getConfiguration('file') == 'none') {
          return;
        }
        $eqlogic = compose::byId($this->getConfiguration('file'));
        $eqlogic->generateComposeConf();
      }
    }

    public function generateComposeConf() {
      $eqLogics = compose::byType('compose', true);
      $file = '---\n';
      $file .= 'version: "3.4"\n';
      $file .= 'services:\n';
      $file .= '\n';
      foreach ($eqLogics as $eqLogic) {
        if ($this->getConfiguration('file') == $this->getId()) {
          $file .= $eqLogic->generateDockerConf();
        }
      }
      $this->writeFile($file);
    }

    public function writeFile($_file) {
      if ($this->getConfiguration('connexion') == 'ssh') {
        file_put_contents('/tmp/ssh.yml', $_file);
        //$this->sendSSH('scp /tmp/ssh.yml');
      } else {
        $this->sendCommand($cmd);
        file_put_contents($this->getConfiguration('path'), $_file);
      }
    }

    public function generateDockerConf() {
      $file = '  ' . $this->getConfiguration('name') . ':\n';
      $file .= '    image: ' . $this->getConfiguration('image') . '\n';
      $file .= '    container_name: ' . $this->getConfiguration('name') . '\n';
      $file .= '    restart: ' . $this->getConfiguration('restart') . '\n';
      if ($this->getConfiguration('privileged')) {
        $file .= '    privileged: true\n';
      }
      if ($this->getConfiguration('environment') != '') {
        $file .= '    environment:\n';
        $explode = explode(';',$this->getConfiguration('environment'));
        foreach ($explode as $line) {
          $file .= '      - ' . $line . '\n';
        }
      }
      if ($this->getConfiguration('volumes') != '') {
        $file .= '    volumes:\n';
        $explode = explode(';',$this->getConfiguration('volumes'));
        foreach ($explode as $line) {
          $file .= '      - ' . $line . '\n';
        }
      }
      if ($this->getConfiguration('ports') != '') {
        $file .= '    ports:\n';
        $explode = explode(';',$this->getConfiguration('ports'));
        foreach ($explode as $line) {
          $file .= '      - ' . $line . '\n';
        }
      }
      $file .= '\n';
      return $file;
    }

    public function sendCommand($_cmd) {
      if ($this->getConfiguration('connexion') == 'ssh') {
        $this->sendSSH($_cmd);
      } else {
        $this->sendShell($_cmd);
      }
    }

    public function sendSSH($_cmd) {
      if (!$connection = ssh2_connect($this->getConfiguration('sshhost'),$this->getConfiguration('sshport','22'))) {
            log::add('sshcommander', 'error', 'connexion SSH KO');
            return 'error connecting';
        }
        if ($this->getConfiguration('sshmode') == "pass") {
            if (!ssh2_auth_password($connection,$this->getConfiguration('sshuser'),$this->getConfiguration('sshpass'))){
                log::add('sshcommander', 'error', 'Authentification SSH KO');
                return 'error connecting';
            }
        } else {
            if (!ssh2_auth_pubkey_file($connection,$this->getConfiguration('sshuser'),$this->getConfiguration('sshkey').'.pub',$this->getConfiguration('sshkey'))){
                log::add('sshcommander', 'error', 'Authentification SSH KO');
                return 'error connecting';
            }
        }
        $result = ssh2_exec($connection, $_cmd . ' 2>&1');
        stream_set_blocking($result, true);
        $result = stream_get_contents($result);

        $closesession = ssh2_exec($connection, 'exit');
        stream_set_blocking($closesession, true);
        stream_get_contents($closesession);

        return $result;
    }

    public function sendShell($_cmd) {
      $cmd = 'sudo ' . $_cmd;
      $result = shell_exec($cmd);
      return $result;
    }

}

class composeCmd extends cmd {
    public function execute($_options = null) {
        if ($this->getType() != 'info') {
          $eqLogic = $this->getEqLogic();
          switch ($this->getLogicalId()) {
              case 'minus1':
              $eqLogic->rmgrocy('1');
              break;
          }
        }
    }
}
?>
