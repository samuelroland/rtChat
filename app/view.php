<?php
/**
 * Mini projet: rtChat
 * Auteur: Samuel Roland
 * But: mettre en pratique l'apprentissage de Ajax et de l'asynchrone
 * Date: juillet 2020.
 */
ob_start();
?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.css">
        <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-grid.css">
        <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-reboot.css">


        <!-- Jquery files -->
        <script src="node_modules/jquery/dist/jquery.js"></script>
        <script src="node_modules/bootstrap/dist/js/bootstrap.js"></script>

        <link rel="stylesheet" href="library.css">
        <link rel="stylesheet" href="chat.css">
        <title>rtChat v1.1</title>
        <script src="global.js"></script>
        <style>
            td {
                padding: 10px;
                background-color: #1abc9c;
            }

            th {
                padding: 10px;
                background-color: #AD0AD3F7;
            }
        </style>
    </head>
    <body>
    <div class="flexdiv">
        <a href="/rtChat/" class="alert-link">
            <div style="background-color: #4eb5e2" class="m-1 p-2">
                <h1 class=" m-1" style="display: inline">rtChat</h1><span>v1.1</span>
            </div>
        </a>
        <?php echo(isset($_SESSION['user']) ? "<h4 class=\"alignright flex-2 p-2 m-1\">Connecté: {$_SESSION['user']['firstname']} {$_SESSION['user']['lastname']}</h4><a href='?action=logout'><button class='p-2 m-1'>Déconnexion</button></a></div>" : "<h4 class='p-2 m-1'>Non connecté...</h4></div>");
        if (isset($_SESSION['user']) == false) {
            ?>
            <form action="?action=login" method="post" class="p-2 m-1">
                <input type="text" placeholder="firstname" name="firstname">
                <input type="password" placeholder="password" name="password">
                <input type="submit" value="Connexion">
            </form>
            <?php
        } else { ?>
            <div class="total flexdiv bg-info">
                <input type="hidden" id="userJson" value='<?= json_encode($_SESSION['user']) ?>'>
                <div id="listConv" class="listConv flex-1 bg-header">
                    <?php foreach ($conversations as $conversation) {
                        if ($conversation['type'] == 1) {  //is a private conversation with 2 persons
                            //Find the member that is not me (called $othermember)
                            if ($conversation['members'][0]['id'] == $_SESSION['user']['id']) {
                                $othermember = $conversation['members'][1];
                            } else {
                                $othermember = $conversation['members'][0];
                            }
                            //When $othermember founded, we can display the conversation info:
                            ?>
                            <div class="oneConv" data-id="<?= $conversation['id'] ?>">
                                <h4 class="d-inline-block"><?= $othermember['firstname'] . " " . $othermember['lastname'] ?></h4>
                                <span class="circle-usericon float-right" id="circleConv-<?= $conversation['id'] ?>"
                                      hidden><p class="marginauto">X</p></span>
                                <br>depuis le <?= date("d.m.Y H:i", strtotime($conversation['startdate'])) ?>

                            </div>
                            <?php
                        } else {    //else it's a group conversation
                            ?>
                            <div class="oneConv" data-id="<?= $conversation['id'] ?>">
                                <h4 class="d-inline-block">Groupe: <?= $conversation['name'] ?></h4>
                                <span class="circle-usericon float-right" hidden
                                      id="circleConv-<?= $conversation['id'] ?>"><p class="marginauto">X</p></span>
                                <br>depuis le <?= date("d.m.Y H:i", strtotime($conversation['startdate'])) ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    <div class="newConv" id="btnNewConv"><strong>Nouvelle conversation</strong></div>
                    <div class="frmnewConv newConv " hidden id="frmNewConv">Avec:
                        <select name="user" id="sltUser">
                        </select>
                        Type de conversation:<br>
                        <input type="radio" name="type" value="1" id="type1" required><label for="type1">Privée</label>
                        <input type="radio" name="type" value="2" id="type2" required><label for="type2">Groupe</label>
                        <div id="divGroupName" class="" hidden>
                            Nom du groupe:
                            <input type="text" placeholder="Nom du groupe" name="groupname" id="inpGroupName">

                        </div>
                        <button id="btnCreateNewConv">Créer</button>
                    </div>
                    <div>
                        <img src="courrier.png" id="imgIcon" class="imgIcon m-2" alt="">
                        <input type="checkbox" id="chkRT" class="" name="chkRT"><label for="chkRT">Realtime</label>
                    </div>
                </div>
                <div class="divMsgs flex-4">
                    <div class="divDetails">
                        <div id="divMsgsDetails">
                            <?php foreach ($messages as $message) {
                                ?>
                                <div class="<?= ($message['sender']['id'] == $_SESSION['user']['id']) ? "box-alignright" : "" ?>">
                                    <div class="oneMsg">
                                        De:
                                        <strong><?= $message['sender']['firstname'] . " " . $message['sender']['lastname'] ?></strong>
                                        <br><em><?= $message['text'] ?></em>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                            <p>Aucune conversation sélectionnée...</p>
                        </div>
                        <div class="divSending flexdiv">
                        <textarea class="fullwidth txtSend" id="txtMsg" rows="2" name="text" maxlength="2000"
                                  placeholder="Envoyer un message..."></textarea>
                            <div class="p-1 divButtons">
                                <button id="btnSend">Envoyer</button>
                                <button id="btnEmpty">Vider</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php
        }
        ?>
    </div>
    </body>
    </html>
<?php
$content = ob_get_clean();
echo $content;
?>