/**
 * Mini projet: rtChat
 * Auteur: Samuel Roland
 * But: mettre en pratique l'apprentissage de Ajax et de l'asynchrone
 * Date: juillet 2020.
 */
let lastConvClicked = null  //global var

$(document).ready(function () {
    EventListenersDeclare()
})

function EventListenersDeclare() {
    btnSend.addEventListener("click", sendMsg)
    btnEmpty.addEventListener("click", function () {
        txtMsg.value = ""
    })
    imgIcon.addEventListener("click", getNewMessages)
    chkRT.addEventListener("change", periodicSearchMessages)
    btnNewConv.addEventListener("click", displayFormNewConv)
    btnCreateNewConv.addEventListener("click", createNewConv)
    type2.addEventListener("RadioStateChange", manageInputGroupName)

    $(".oneConv").on("click", function (event) { //on event click with objects with class .oneConv
        lastConvClicked = event.target  //save the last conv clicked for apply a darker background if request was successful
        if (event.target.getAttribute("data-id") != null) {
            getConversation(lastConvClicked)
            removeBG()
        }
        event.stopPropagation()
    })
    //Load the informations of the user logged:
    user = JSON.parse(userJson.value)
    loadListConvs()
    periodicSearchMessages()    //if the chkRT is checked, the realtime mode can start
}

async function getConversation(lastConvClicked) {
    //Transform lastConvClicked en tableau de listOfConvs:
    newConvTransformed = null
    console.log("getconverstaion function")

    console.log(listOfConvs)
    Array.prototype.forEach.call(listOfConvs, function (convRun) {
        if (convRun.id == lastConvClicked.getAttribute("data-id")) {
            newConvTransformed = convRun
        }
    })

    url = "?action=getMessages&id=" + newConvTransformed.id
    Promise.all([reqGET(url, newConvTransformed)])
    divMsgsDetails.innerHTML = ""   //make the content empty to remove messages
}

async function reqGET(url, convInRun) {
    req = new XMLHttpRequest()

    req.open("GET", url)
    req.send()
    req.onreadystatechange = function () {
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            const response = JSON.parse(this.responseText)
            console.log("ready!")
            console.log(response)
            displayConversation(response, convInRun)
        }
    }
}


function displayConversation(res, convInRun) {
    console.log("displayConversation")
    if (lastConvClicked != undefined) { //if undefined, the conv that have receive messages is not displayed
        if (convInRun.id == lastConvClicked.getAttribute("data-id")) {    //if messages are in the current conv displayed
            if (res.hasOwnProperty("error") == false) {
                checkChatZoneAboutErrorMsg()
                document.getElementById(convInRun.circleid).hidden = true
                //Display the messages of the conversation:
                Array.prototype.forEach.call(res, function (msg) {
                    divBig = document.createElement("div")
                    divSmall = document.createElement("div")
                    divBig.appendChild(divSmall)
                    if (user.id == msg.sender.id) {
                        divBig.classList.add("box-alignright")
                    }
                    divSmall.innerHTML = "De: <strong>" + msg.sender.firstname + " " + msg.sender.lastname + "</strong><br><em>" + msg.text + "</em><br><div class='alignright fullwidth'>" + msg.time + "</div>"
                    divSmall.classList.add("oneMsg")
                    divMsgsDetails.appendChild(divBig)

                    //Set last id of msg in the right conversation:
                    index = 0
                    Array.prototype.forEach.call(listOfConvs, function (convRun) {
                        if (convRun.id == convInRun.id) {
                            if (msg.id == null) {
                                listOfConvs[index].lastMsgId = 1000
                            } else {
                                listOfConvs[index].lastMsgId = msg.id
                            }
                        }
                        index++
                    })
                })

                lastConvClicked.classList.add("convSelect") //mark the last conv clicked as selected
            } else {
                if (res.error.id == 3) {    //if conversation doesn't have any message yet
                    lastConvClicked.classList.add("convSelect") //mark the last conv clicked as selected
                    divMsgsDetails.innerHTML = "<p>" + res.error.text + "</p>"
                }
            }
        } else { //if messages are in other conversations, just add the notifs counter:
            addNotifsCounter(res, convInRun)
        }
    } else {    //if messages are in other conversations, just add the notifs counter:
        addNotifsCounter(res, convInRun)
    }
}

function addNotifsCounter(res, convInRun) {
    console.log(listOfConvs)
    console.log(convInRun)
    let circleid = null
    Array.prototype.forEach.call(listOfConvs, function (convRun) {
        if (convRun.id == convInRun.id) {
            circleid = convRun.circleid
            console.log("circleid trouvé " + circleid)
        }
    })

    circle = document.getElementById(circleid)
    if (res.length != undefined) {
        circle.hidden = false
        circle.firstChild.innerText = res.length
    } else {
        circle.hidden = true
    }
}

function checkChatZoneAboutErrorMsg() {
    //If there is an error message in the chat, delete the content of the chat to clear the error message. The error message is wroten in a p markup
    if (divMsgsDetails.hasChildNodes()) {
        if (divMsgsDetails.firstChild.tagName == "P") {
            divMsgsDetails.removeChild(divMsgsDetails.firstChild)
        }
    }
}

function addMsgSent(msgSent) {
    if (msgSent.hasOwnProperty("error") == false) {

        checkChatZoneAboutErrorMsg()

        divBig = document.createElement("div")
        divSmall = document.createElement("div")
        divBig.appendChild(divSmall)
        if (user.id == msgSent.sender.id) {
            divBig.classList.add("box-alignright")
        }
        divSmall.innerHTML = "De: <strong>" + msgSent.sender.firstname + " " + msgSent.sender.lastname + "</strong><br><em>" + msgSent.text + "</em><br><div class='alignright fullwidth'>" + msgSent.time + "</div>"
        divSmall.classList.add("oneMsg")
        divMsgsDetails.appendChild(divBig)
        //Set last id of msg in the right conversation:
        index = 0
        Array.prototype.forEach.call(listOfConvs, function (convRun) {
            if (convRun.id == lastConvClicked.getAttribute("data-id")) {
                listOfConvs[index].lastMsgId = msgSent.id
            }
            index++
        })
    } else {
        divMsgsDetails.innerHTML = msgSent.error.text
    }
}

function removeBG() {
    var els = document.getElementsByClassName("oneConv");
    Array.prototype.forEach.call(els, function (el) {
        el.classList.remove("convSelect")
    })
}

function sendMsg() {
    if (txtMsg.value != "" && lastConvClicked != null) {
        if (lastConvClicked.getAttribute("data-id") != null) {  //if clicked on a child of a .oneConv div
            req = newReq()
            req.onreadystatechange = function () {
                if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                    const response = JSON.parse(this.responseText)
                    addMsgSent(response)
                    console.log("ajouté à la covn...")
                    txtMsg.value = ""   //empty the txtMsg because message has been sent!
                }
            }
            req.open("POST", "?action=sendMsg")
            req.setRequestHeader("Content-Type", "application/json")
            body = {
                text: txtMsg.value,
                conversation_id: lastConvClicked.getAttribute("data-id")
            }
            req.send(JSON.stringify(body))
        }
    }
}

function newReq() {
    return new XMLHttpRequest()
}

function getNewMessages() {
    console.log("départ debug")
    console.log(listOfConvs)
    Array.prototype.forEach.call(listOfConvs, function (convInRun) {
        console.log(convInRun)
        idmsg = convInRun.lastMsgId
        idconv = convInRun.id
        if (idmsg == undefined) {
            idmsg = 1
        }
        if (idconv != undefined) {
            url = "?action=getMessagesAfterId&idmsg=" + idmsg + "&idconv=" + idconv
            reqGET(url, convInRun)
        }
    })

}

idSetInterval = null

function periodicSearchMessages() {
    if (chkRT.checked == true) {
        idSetInterval = setInterval(getNewMessages, 1000)
    } else {
        clearInterval(idSetInterval)
    }
}

async function GETUsersList() {
    req = new XMLHttpRequest()
    url = "?action=getUsers"
    req.open("GET", url)
    req.send()
    req.onreadystatechange = function () {
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            const response = JSON.parse(this.responseText)
            listUsers(response)
        }
    }
}

function listUsers(users) {
    sltUser.innerHTML = ""
    Array.prototype.forEach.call(users, function (user) {
        option = document.createElement("option")
        option.value = user.id
        option.innerText = user.firstname + " " + user.lastname
        sltUser.appendChild(option)
    })
}

function displayFormNewConv() {
    frmNewConv.hidden = false
    GETUsersList()
}

function createNewConv() {
    if (type1.checked == true || type2.checked == true && sltUser.options.length != 0) {
        req = newReq()
        req.onreadystatechange = function () {
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                const response = JSON.parse(this.responseText)
                addNewConv(response)
                console.log("ajouté new conv ...")
                frmNewConv.hidden = true
            }
        }
        req.open("POST", "?action=createConv")
        req.setRequestHeader("Content-Type", "application/json")

        if (type1.checked) {
            type = 1
        } else {
            type = 2
        }
        body = {
            user: sltUser.options[sltUser.options.selectedIndex].value,
            type: type
        }
        if (inpGroupName.value != "") {
            body.groupname = inpGroupName.value
        }
        req.send(JSON.stringify(body))
    }
}

//Add new conversation to the list of conversations
function addNewConv(conv) {
    divConv = document.createElement("div")
    divConv.classList.add("oneConv")
    divConv.setAttribute("data-id", conv.id)

    //Create the text displayed depending on the conversation type (private or group):
    if (conv.type == 1) {
        //Search the other member:
        if (conv.members[0].id == user.id) {
            othermember = conv.members[1]
        } else {
            othermember = conv.members[0]
        }

        divConv.innerHTML = "<h4>" + othermember.firstname + " " + othermember.lastname + "</h4>"
    } else {
        divConv.innerHTML = "<h4>Groupe: " + conv.name + "</h4>"
    }
    divConv.innerHTML += "depuis le " + conv.simpledatetime
    divConv.innerHTML += "<span class=\"circle-usericon float-right\" id=\"circleConv-3\" hidden><p class=\"marginauto\">X</p></span>"

    listConv.insertBefore(divConv, listConv.children[listOfConvs.length])  //insert before the div after the last .oneConv (so after the last .oneConv... --> length - 1 + 1)
    EventListenersDeclare() //to add eventlistener on the new .oneConv
}

function manageInputGroupName() {
    if (type2.checked == true) {
        divGroupName.hidden = false
    } else {
        divGroupName.hidden = true
    }
}

let listOfConvs = []

function loadListConvs() {
    console.log("reload list convs")
    counter = 0
    //Count the number of conversations loaded and make an array with:
    var els = document.getElementsByClassName("oneConv");
    Array.prototype.forEach.call(els, function (el) {
        listOfConvs[counter] = []
        listOfConvs[counter].id = el.getAttribute("data-id")
        listOfConvs[counter].circleid = "circleConv-" + el.getAttribute("data-id")
        counter++
    })
}