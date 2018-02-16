$(document).ready(function() {
    console.log("ready!");
    $.ajaxSetup({
        url: 'db.php',
        type: 'post',
        cache: 'false',
        success: function(data) {
            console.log(data);
        },
        error: function() {
            alert('Ajax failed');
        }
    });
});

function Start(){
	//Verifies the ID is entered
	Verify_ID_Field();
	//Verifies the vote button is selected
	Verify_Vote_Buttons();
	Voter_To_CLA(Voter_ID, Vote);
}

function Results(){
	var action = "Results";
	var Ajax_Data = {
		action: action
	};
	Outgoing_Ajax(Ajax_Data);
}

function Verify_ID_Field(){
	if (document.getElementById("Voter_ID").value.length == 0){
		alert("Enter a type ID");
		throw new Error('Enter a type ID');
	}
	else{
		if (document.getElementById("Voter_ID").value > 2147483647)
		{
			alert("Pick a number smaller than 2147483647");
			throw new Error('Pick a number smaller than 2147483647');
		}
		Voter_ID = Grab_Voter_ID();
	}
}

function Grab_Voter_ID(){
	Voter_ID = document.getElementById("Voter_ID").value;
	return Voter_ID;
}

function Verify_Vote_Buttons(){
	if (!document.getElementById("Red").checked && !document.getElementById("Blue").checked){
		alert("Please pick a team");
		throw new Error('Please pick a team');
	}
	else{
		Vote = Grab_Vote();
	}
}

function Grab_Vote(){
	if (document.getElementById("Red").checked){
		Vote = "Red";
	}
	else{
		Vote = "Blue";
	}
	return Vote;
}

function Outgoing_Ajax(Ajax_Data) {
    Incoming_Ajax_Data = $.ajax({
        data: Ajax_Data
    }).responseText;
    return Incoming_Ajax_Data;
}

//Data being sent to db.php
function Reset_JS(){
	var action = "Reset";
	var Ajax_Data = {
		action: action
	};
	Outgoing_Ajax(Ajax_Data);
}

//Sends the voter ID and the vote to db.php
function Voter_To_CLA(Voter_ID, Vote){
	var action = "Voter_To_CLA";
	var Ajax_Data = {
		Voter_ID: Voter_ID,
		Vote: Vote,
		action: action
	};
	Outgoing_Ajax(Ajax_Data);
}
