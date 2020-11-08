<?php
/**
 * Mini projet: rtChat
 * Auteur: Samuel Roland
 * But: mettre en pratique l'apprentissage de Ajax et de l'asynchrone
 * Date: juillet 2020.
 */

//Get all message from a conversation by id of the conversation
function getMessages($id)
{
    $messages = getAllMessages($id);
    if (empty($messages)) {
        if (convExists($id)) {
            $error = [
                "error" => [
                    "id" => 3,
                    "text" => "Aucun message dans cette conversation... écrivez un premier msg !"
                ]
            ];
        } else {
            $error = [
                "error" => [
                    "id" => 2,
                    "text" => "Conversation non trouvée..."
                ]
            ];
        }
        echo json_encode($error);
    } else {
        echo json_encode($messages);
    }
}

function getMessagesAfterId($idmsg, $idconv)
{
    $messages = getMessagesAfter($idmsg, $idconv);
    if (empty($messages)) {
        $error = [
            "error" => [
                "id" => 1,
                "text" => "Aucun nouveau message..."
            ]
        ];
        echo json_encode($error);
    } else {
        echo json_encode($messages);
    }
}

//Send a message to a receiver
function sendMsg($data)
{
    $msg['text'] = $data['text'];
    $msg['date'] = date("Y-m-d H:i:s", time());
    $msg['sender_id'] = $_SESSION['user']['id'];
    $msg['conversation_id'] = $data['conversation_id'];
    $idInserted = createMsg($msg);    //create with msg with 4 fields

    //add 3 fields for send back a response with more data
    $msg['id'] = $idInserted;
    $msg['time'] = date("H:i", strtotime($msg['date']));
    $msg['sender'] = getOne("users", $msg['sender_id']);
    echo json_encode($msg); //write the response in json format for the ajax call
}


//Login the user
function login($info)
{
    //Get the user with his firstname (firstname is unique!)
    $theUser = getByCondition("users", ["firstname" => $info['firstname']], "firstname =:firstname", false);

    if ($theUser != null) { //if user has been found...
        if (password_verify($info['password'], $theUser['password']) == true) { //if login info are right
            unset($theUser['password']);
            $_SESSION['user'] = $theUser;   //log the user
        }
        home();
    } else {
        home();
    }
}

function logout()
{
    unset($_SESSION['user']);
    home();
}

function home()
{
    $conversations = getConversations($_SESSION['user']['id']);
    require "view.php";
}

function getUsers()
{
    $users = getAllUsers();
    if (empty($users)) {
        $error = [
            "error" => [
                "text" => "Aucun utilisateurs trouvé..."
            ]
        ];
        echo json_encode($error);
    } else {
        echo json_encode($users);
    }
}

function createConv($data)
{
    //Bring together informations of the conversation before create it:
    if ($data['type'] == 2) {
        $conv['type'] = 2;
        $conv['name'] = $data['groupname'];
    } else {
        $conv['type'] = 1;  //if not 2 the type is equal to 1 (because no other values than 1 or 2 are accepted
    }
    $conv['startdate'] = date("Y-m-d H:i:s", time());
    $idInserted = createOneConversation($conv);

    //Add members to the conversation (table "interact"):
    $interact1['conversation_id'] = $idInserted;
    $interact2['conversation_id'] = $idInserted;

    $interact1['user_id'] = $_SESSION['user']['id'];
    $interact2['user_id'] = $data['user'];

    createOneInteract($interact1);
    createOneInteract($interact2);

    //Prepare and return the new conversation informations:
    $newConv = getOneConversation($_SESSION['user']['id'], $idInserted);
    $newConv['simpledatetime'] = date("d.m.Y H:i", strtotime($conv['startdate']));

    if (empty($newConv)) {
        $error = [
            "error" => [
                "text" => "Problème de création ou de lecture de la nouvelle conversation..."
            ]
        ];
        echo json_encode($error);
    } else {
        echo json_encode($newConv);
    }
}

?>