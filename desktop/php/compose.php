<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'compose');
$eqLogics = eqLogic::byType('compose');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4" id="hidCol" style="display: none;">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-12 eqLogicThumbnailDisplay" id="listCol">

    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">

      <div class="cursor eqLogicAction" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br/>
        <span>{{Configuration}}</span>
      </div>

    </div>

    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />


    <legend><i class="fas fa-home" id="butCol"></i>  {{Mes Equipements}}</legend>
    <div class="eqLogicThumbnailContainer">
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/compose/plugin_info/compose_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure">
          <i class="fa fa-cogs"></i>
          {{Configuration avancée}}
        </a>
        <a class="btn btn-default btn-sm eqLogicAction" data-action="copy">
          <i class="fas fa-copy"></i>
          {{Dupliquer}}
        </a>
        <a class="btn btn-sm btn-success eqLogicAction" data-action="save">
          <i class="fas fa-check-circle"></i>
          {{Sauvegarder}}
        </a>
        <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove">
          <i class="fas fa-minus-circle"></i>
          {{Supprimer}}
        </a>
      </span>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement compose}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  foreach (jeeObject::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-3">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                  echo '<label class="checkbox-inline">';
                  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                  echo '</label>';
                }
                ?>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" ></label>
              <div class="col-sm-8">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Type d'Equipement}}</label>
              <div class="col-sm-3">
                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" id="type">
                  <option value="file">{{Docker Compose}}</option>
                  <option value="docker">{{Docker}}</option>
                </select>
              </div>
            </div>


            <div id="compose">
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Chemin du fichier de configuration}}</label>
                <div class="col-sm-3">
                  <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="path" type="text" placeholder="{{saisir le chemin du fichier compose}}">
                </div>
              </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Type de connexion}}</label>
              <div class="col-sm-3">
                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="connexion" id="connexion">
                  <option value="local">{{Locale}}</option>
                  <option value="ssh">{{SSH}}</option>
                </select>
              </div>
            </div>

            <div id="ssh">
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Adresse IP}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshhost" type="text" placeholder="{{saisir l'adresse IP}}">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Port SSH}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshport" type="text" placeholder="{{saisir le port SSH}}">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Identifiant}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshuser" type="text" placeholder="{{saisir le login}}">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Mode d'Authentification}}</label>
              <div class="col-sm-3">
                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshmode" id="auth">
                  <option value="pass">{{Mot de Passe}}</option>
                  <option value="key">{{Echange de Clef}}</option>
                </select>
              </div>
            </div>

            <div class="form-group" id="key">
              <label class="col-sm-3 control-label">{{Clef SSH}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshkey" type="text" placeholder="/var/www/.ssh/id_rsa">
              </div>
            </div>


            <div class="form-group" id="password">
              <label class="col-sm-3 control-label">{{Mot de Passe}}</label>
              <div class="col-sm-3">
                <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sshpass" placeholder="{{Mot de Passe}}">
              </div>
            </div>
          </div>
          </div>

            <div id="docker">
              <div class="form-group">
                <label class="col-sm-3 control-label">{{Membre du Compose}}</label>
                <div class="col-sm-3">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="file">
                    <option value="none">{{Aucun}}</option>
                    <?php
                    $eqLogics = compose::byType('compose');
                    foreach ($eqLogics as $eqLogic) {
                      if ($eqLogic->getConfiguration('type') == 'file') {
                        echo '<option value="' . $eqLogic->getId() . '">' . $eqLogic->getName() . '</option>';
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom du container}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="name">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Image du container}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="image">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Variables d'environnement}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="environment">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Volumes}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="volumes">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Ports}}</label>
              <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ports">
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">{{Mode de Fonctionnement}}</label>
              <div class="col-sm-3">
                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="restart">
                  <option value="unless-stopped">{{Unless Stopped}}</option>
                  <option value="always">{{Always}}</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" >Mode Privilèges</label>
              <div class="col-sm-3">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="privileged">{{Activer}}</label>
              </div>
            </div>

          </div>

          </fieldset>
        </form>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th style="width: 150px;">{{Nom}}</th>
              <th style="width: 100px;"></th>
              <th style="width: 150px;">{{Paramètres}}</th>
              <th style="width: 100px;"></th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>

<?php include_file('desktop', 'compose', 'js', 'compose'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
