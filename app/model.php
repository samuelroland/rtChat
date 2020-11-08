<?php
/**
 * Mini projet: rtChat
 * Auteur: Samuel Roland
 * But: mettre en pratique l'apprentissage de Ajax et de l'asynchrone
 * Date: juillet 2020.
 */

//Get all message from a conversation by id
function getAllMessages($id)
{
    $messages = getByCondition("messages", ["id" => $id], "messages.conversation_id =:id ORDER BY messages.date", true);

    //Add sender informations:
    foreach ($messages as $key => $message) {
        $messages[$key]['sender'] = getOne("users", $message['sender_id']);
        $messages[$key]['time'] = date("H:i", strtotime($message['date']));
    }
    return $messages;
}

//Send a message to a receiver
function createMsg($msg)
{
    return createOne("messages", $msg);
}

//Get all conversation of the user logged
function getConversations($id)
{
    $query = "SELECT conversations.id, conversations.name, conversations.startdate, conversations.type FROM conversations 
INNER JOIN interact ON interact.conversation_id = conversations.id
INNER JOIN users ON users.id = interact.user_id
WHERE users.id =:id
";

    $conversations = Query($query, ['id' => $id], true);

    foreach ($conversations as $key => $conversation) {
        $conversations[$key]['members'] = getMembersFromAConversation($conversation['id']);
    }
    return $conversations;
}

//Test if a conversation exists:
function convExists($id)
{
    $test = getOne("conversations", $id);
    return !empty($test);
}

//Get ONE conversation of the user given
function getOneConversation($iduser, $idconv)
{
    $query = "SELECT conversations.id, conversations.name, conversations.startdate, conversations.type FROM conversations 
INNER JOIN interact ON interact.conversation_id = conversations.id
INNER JOIN users ON users.id = interact.user_id
WHERE users.id =:iduser AND conversations.id =:idconv";

    $conv = Query($query, ['iduser' => $iduser, "idconv" => $idconv], false);

    $conv['members'] = getMembersFromAConversation($conv['id']);
    return $conv;
}

//Get messages of a conversation that are written after a message taken by id (so all messages with a bigger id)
function getMessagesAfter($idmsg, $idconv)
{
    $messages = getByCondition("messages", ["idconv" => $idconv, "idmsg" => $idmsg], "messages.conversation_id =:idconv AND messages.id >:idmsg ORDER BY messages.date", true);

    //Add sender informations:
    foreach ($messages as $key => $message) {
        $messages[$key]['sender'] = getOne("users", $message['sender_id']);
        $messages[$key]['time'] = date("H:i", strtotime($message['date']));
    }
    return $messages;
}

function getMembersFromAConversation($id)
{
    $query = "SELECT users.id, users.firstname, users.lastname FROM interact 
INNER JOIN users on interact.user_id = users.id
WHERE interact.conversation_id =:id";

    $members = Query($query, ['id' => $id], true);
    return $members;
}

function getAllUsers()
{
    $query = "SELECT id, firstname, lastname from users;";
    return Query($query, [], true);
}

function createOneConversation($conv)
{
    return createOne("conversations", $conv);
}

function createOneInteract($interact)
{
    return createOne("interact", $interact);
}


?>