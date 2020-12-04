
// ajax.js
document.getElementById("eventStuff").style.visibility = 'hidden';
document.getElementById("message").style.visibility = 'hidden';
let username = document.getElementById("username").value; // Get the username from the form
let password = document.getElementById("password").value; // Get the password from the form
//we put them outside all of the methods so that they could be global

//function to escape output
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function loginAjax(event) {
    username = document.getElementById("username").value; // Get the username from the form
    password = document.getElementById("password").value; // Get the password from the form
    // Make a URL-encoded string for passing POST data:
    let data = { "username": username, "password": password };
    fetch("login_ajax.php", {//sending the data
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'content-type': 'application/json' }
        })
        .then(response => response.json())
        .then(data =>  {//getting the response
            console.log(data.success ? "You've been logged in!" : `You were not logged in ${data.message}`)
            if (data.success == true) {
                document.getElementById("loginStuff").style.visibility = 'hidden'; //hide the authentification features
                document.getElementById("eventStuff").style.visibility = 'visible'; //open up event functionality  
                getEvents_login(); //getting the events of the user
                updateCalendar(); //updating the calendar
            }
        })//these will reset the input content
        .then(document.getElementById("username").value='')
        .then(document.getElementById("password").value='')
        .catch(err => console.error(err));

}
//checks to see if user in logged in upon loading the page
function isLoggedIn(event) {
    let x = new XMLHttpRequest();
    x.open('POST', 'isLoggedIn_ajax.php', true);
    x.send();
    x.onload = function() {
        data = JSON.parse(x.responseText);
        if (data.success == true) {
            getEvents_login();//update the calendar if user is logged  in
            document.getElementById("loginStuff").style.visibility='hidden';
            document.getElementById("eventStuff").style.visibility='visible';
        }
        else {
            document.getElementById("loginStuff").style.visibility='visible';
            document.getElementById("eventStuff").style.visibility='hidden';
        }
    }

}

document.addEventListener("DOMContentLoaded", isLoggedIn, false);

let XMLstuff = new XMLHttpRequest(); 
let XMLcategories = new XMLHttpRequest(); 
let events_data = '';
function getEvents_login() {
    XMLstuff.open('POST', 'show_events_ajax.php', true); //checking the user validity server side
    XMLstuff.send();
    XMLstuff.onload = function() {
        events_data = JSON.parse(XMLstuff.responseText);
        updateCalendar(events_data); //updating the calendar with the specific user events

    };  
}


document.getElementById("createEvent_button").addEventListener("click", inviteUser, false);

//this function will display events of a specified category
function getEventsByCategory() {
    let category = document.getElementById("showCategories").value;
    if (category == "") {
        getEvents_login();
    }
    else {
        let data = { "category": category };
        fetch("get_events_category_ajax.php", {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'content-type': 'application/json' }
        })
        .then(response => response.json())
        .then(res => updateCalendar(res)) //updating the calendar with the new specified events
        .then(document.getElementById("showCategories").value='')//reset the input content
        .catch(err => console.error(err));

        //adding in a usability message
        document.getElementById("message").style.visibility = 'visible';
    }
}

document.getElementById("sendCategory").addEventListener("click", getEventsByCategory, false);

//creating the calendar grid
let thisYear = 2020; 
let thisMonth = 2;
let currentMonth = new Month(thisYear, thisMonth); //March 2020 by default
//anonymous function that jumps the calendar up a year
document.getElementById("next_year_btn").addEventListener("click", function(event) {
    thisYear = thisYear + 1;
    currentMonth = new Month(thisYear, thisMonth);
    getEvents_login();
}, false);

//anonymous function that takes the calendar back a year
document.getElementById("prev_year_btn").addEventListener("click", function(event) {
    thisYear = thisYear - 1;
    currentMonth = new Month(thisYear, thisMonth);
    getEvents_login();
}, false);

// Change the month when the "next" button is pressed
document.getElementById("next_month_btn").addEventListener("click", function(event){
    currentMonth = currentMonth.nextMonth();
}, false);
document.getElementById("next_month_btn").addEventListener("click", getEvents_login, false);

//Change the month when "previous" button is pressed
document.getElementById("prev_month_btn").addEventListener("click", function(event) {
	currentMonth = currentMonth.prevMonth();
}, false);
document.getElementById("prev_month_btn").addEventListener("click", getEvents_login, false);

//main function that displays the calendar and supports event viewing
function updateCalendar(events_data){

    //arrays to access the English names of the months
	let days_of_week = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
	let months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

	//sets up the month display and the nodes for previous and following months
	let HTMLmonth = document.getElementById("monthName");
	HTMLmonth.innerHTML = "";
	let newMonth = document.createElement("h1");
	let current_month_name = months[currentMonth.month]; 
	let x = document.createTextNode(htmlEntities(current_month_name + " " + currentMonth.year));
	newMonth.appendChild(x);
	HTMLmonth.appendChild(newMonth);

    //gets the elements we need to create week and day items in our grid
	let weeks = currentMonth.getWeeks();
	let HTMLWeeks = document.getElementById("calendar");
	HTMLWeeks.innerHTML = "";



	for(let w in weeks){
		let days = weeks[w].getDates();		
		for(let d in days){
			//create elements in DOM
			let dayElement = document.createElement("div");
			dayElement.className = "grid-item"; 
			dayElement.append(document.createTextNode(htmlEntities((days_of_week[(days[d].getDay())]))));
			dayElement.append(document.createElement("br"));
			dayElement.append(document.createTextNode(htmlEntities((days[d].getDate()))));
            HTMLWeeks.appendChild(dayElement);
            //for loop for events
            if (typeof events_data !== 'undefined'){
                for (let ue in events_data.events){
                    //getting all of the relevant information for the day we are on
                    //as well as for the current event being considered
                    let yr1 =  parseInt(events_data.events[ue].date.substring(0,4));
                    let yr2 =parseInt(days[d].getFullYear());
                    let m1 = parseInt(events_data.events[ue].date.substring(5,7)) - 1;
                    let m2 = parseInt(days[d].getMonth());
                    let d1 = parseInt(events_data.events[ue].date.substring(8,11));
                    let d2 = parseInt(days[d].getDate());
                    
                    //checking to see if the date credentials match
                    if (d1==d2&&yr1==yr2&&m1==m2){
                        let title = events_data.events[ue].title;
                        let time =  events_data.events[ue].time;
                        let showEvent = document.createElement('p'); 
                        showEvent.append(document.createTextNode(htmlEntities(title + ", " + time))); 
                        dayElement.appendChild(showEvent); 
                    }
                }

            }
   
		}
	}
}
document.addEventListener("DOMContentLoaded", updateCalendar, false); 
document.getElementById("logout_button").addEventListener("click", updateCalendar, false);
document.getElementById("login_btn").addEventListener("click", loginAjax, false); // Bind the AJAX call to button click

//this function logs the user out
function logoutAjax(event) {
    fetch("logout_ajax.php", {//call to the backend php script that will destroy the session
        method: 'POST', //changed to POST
        headers: { 'content-type': 'application/json' }
    })
    .then(response => response.json())
    .then(res => console.log(res));
    console.log("logged out");
    //hide the event management functionality
    //bring back the registration/log in information
    document.getElementById("loginStuff").style.visibility = 'visible';
    document.getElementById("eventStuff").style.visibility = 'hidden';
    thisMonth=2; 
    thisYear = 2020;
    currentMonth = new Month(thisYear, thisMonth);
    updateCalendar(); //hide the events
    document.getElementById("message").style.visibility='hidden';

}

document.getElementById("logout_button").addEventListener("click", logoutAjax, false);

//this function creates a new user
function createNewUser(event) {
    const newUsername = document.getElementById("newUser").value; // Get the username from the form
    const newPassword = document.getElementById("newPassword").value; // Get the password from the form
    const data = { "username": newUsername, "password": newPassword };
        fetch("newUser_ajax.php", {//send the credentials to the backend
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'content-type': 'application/json' }
        })
        .then(response => response.json())
        .then((data) => {
            if(data.success == false) {//if the username already exists
                console.log("Username already taken");
            }
        })
        .then(document.getElementById("newUser").value='')
        .then(document.getElementById("newPassword").value='')
        .catch(err => console.error(err));
}
document.getElementById("newUserButton").addEventListener("click", createNewUser, false); // Bind the AJAX call to button click

//this function creates new events, with a few optional fields for category assignment and event sharing
function createNewEvent(event) {
    const eventName = document.getElementById("eventName").value;
    const eventDate = document.getElementById("eventDate").value;
    const eventTime = document.getElementById("eventTime").value;
    const category = document.getElementById("category").value;
    const otherUser = document.getElementById("inviteUser").value;
    const token = document.getElementById("tokenDelete").value;

    const data = { "name": eventName, "date": eventDate, "time": eventTime, "category": category, "otherUser" : otherUser, 'token' : token};
        fetch("addEvent_ajax.php", {//AJAX call
            method: 'POST',
            body: JSON.stringify(data),
            headers: {'content-type': 'application/json' }
        })
        .then(response => response.json())
        .then((data)=> {
            if (data.success == true) {
                getEvents_login(); //this adds the new event to the calendar
                //updateCalendar();
            }
        })//resetting the input content
        .then(document.getElementById("eventName").value='')
        .then(document.getElementById("eventDate").value='')
        .then(document.getElementById("eventTime").value="")
        .then(document.getElementById("category").value='')
        .then(document.getElementById("inviteUser").value='')
        .catch(err => console.error(err));
}
document.getElementById("createEvent_button").addEventListener("click", createNewEvent, false);

//delete event function
function deleteEvent(event) {
    const eventName = document.getElementById("eventNameDelete").value; //get the title of the event to be deleted
    const eventDate = document.getElementById("eventDateDelete").value;
    const token = document.getElementById("tokenDelete").value;
    const data = { "name": eventName, "date": eventDate, "time": eventTime, 'token' : token }; 
    fetch("deleteEvent_ajax.php", {//AJAX call
        method: 'POST',
        body: JSON.stringify(data),
        headers: {'content-type': 'application/json' }
    })
    .then(response => response.json())
    .then((data)=> {
        getEvents_login(); //update the calendar to take away the deleted event
        updateCalendar();
    })//these .then statements reset the input content
    .then(document.getElementById("eventNameDelete").value='')
    .then(document.getElementById("eventDateDelete").value='')
    .catch(err => console.error(err));

}
document.getElementById("deleteEvent_button").addEventListener("click", deleteEvent, false);

//function to modify events
function editEvent(event) {
    //get all of the relevant data
    const eventNameOriginal = document.getElementById("eventNameToEdit").value;
    const eventDateOriginal = document.getElementById("eventDateToEdit").value;
    const eventTimeOriginal = document.getElementById("eventTimeToEdit").value;
    const categoryOriginal = document.getElementById("categoryToEdit").value;
    const sharedUserOriginal = document.getElementById("shareEdit").value;
    const eventNameNew = document.getElementById("eventNameNew").value;
    const eventDateNew = document.getElementById("eventDateNew").value;
    const eventTimeNew = document.getElementById("eventTimeNew").value;
    const categoryNew = document.getElementById("categoryNew").value;
    const otherUserNew = document.getElementById("inviteUserNew").value;
    const token = document.getElementById("tokenDelete").value;

    const data = { "original name": eventNameOriginal, "original date": eventDateOriginal, "original time": eventTimeOriginal, "original category": categoryOriginal, 'original shared user' : sharedUserOriginal,
                     "new name": eventNameNew, "new date": eventDateNew, "new time": eventTimeNew, "new category": categoryNew, "new shared user": otherUserNew, 'token' : token};
    fetch("editEvent_ajax.php", {
        method: 'POST',
        body: JSON.stringify(data),
        headers: {'content-type': 'application/json' }
    })
    .then(response => response.json())
    .then((data)=> {
        if(data.success == true) {
            getEvents_login();
        }
    })
    .then(document.getElementById("eventNameToEdit").value='')
    .then(document.getElementById("eventDateToEdit").value='')
    .then(document.getElementById("eventTimeToEdit").value='')
    .then(document.getElementById("categoryToEdit").value='')
    .then(document.getElementById("eventNameNew").value='')
    .then(document.getElementById("eventDateNew").value='')
    .then(document.getElementById("eventTimeNew").value='')
    .then(document.getElementById("categoryNew").value='')
    .then(document.getElementById("inviteUserNew").value='')
    .then(document.getElementById("shareEdit").value='')
    .catch(err => console.error(err));
    //these reset the input fields
}
document.getElementById("editEvent_button").addEventListener("click", editEvent, false);