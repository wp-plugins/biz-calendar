jQuery(document).ready( function($){
	if ( $("#biz_calendar").size() == 0 ){
		return;
	}
	bizCalendar();
});

var currentSetting = null;

var bizCalendar = function(){
	var options = window.bizcalOptions;
	var holidays = window.bizcalHolidays;
	var now = new Date();

	var setting = {
		year : now.getFullYear(),
		month : now.getMonth() + 1,
		options : options,
		holidays: holidays
	};
	window.currentSetting = setting;
	document.getElementById('biz_calendar').innerHTML = getCalendar( setting );
}

function downMonth(){
	if ( currentSetting.month == 1){
		currentSetting.month = 12;
		currentSetting.year = currentSetting.year -1;
	}else{
		currentSetting.month = currentSetting.month - 1;
	}
	document.getElementById('biz_calendar').innerHTML = getCalendar( currentSetting );
}

function upMonth(){
	if ( currentSetting.month == 12){
		currentSetting.month = 1;
		currentSetting.year = currentSetting.year + 1;
	}else{
		currentSetting.month = currentSetting.month + 1;
	}
	document.getElementById('biz_calendar').innerHTML = getCalendar( currentSetting );
}

function goToday(){
	var now = new Date();
	currentSetting.year = now.getFullYear();
	currentSetting.month = now.getMonth() + 1;
	document.getElementById('biz_calendar').innerHTML = getCalendar( currentSetting );
}

var getCalendar = function( setting ){
	var weekArray = new Array("日","月","火","水","木","金","土");
	var start_day = getStartDayOfMonth( setting.year, setting.month);
	var last_date = getEndDateOfMonth( setting.year, setting.month);
	var calLine = Math.ceil((start_day + last_date ) / 7);
	var calArray   = new Array( 7 * calLine );

	//カレンダーの日付テーブル作成
	for ( var i = 0; i < 7 * calLine; i++){
		if ( i >= last_date ){
			break;
		}
		calArray[i + start_day] = i + 1;
	}

	//カレンダーのタイトル
	var title = setting.year + "年" + setting.month + "月";
	var html = "<table class='bizcal' ><tr>";
		html += "<td id='downMonth' onclick='downMonth()' title='前の月へ' >＜</td>";
		html += "<td colspan='4'>" + title + "</td>";
		html += "<td id='upMonth' onclick='upMonth()' title='次の月へ' >＞</td>";
		html += "<td id='goToday' onclick='goToday()' title='今月へ' >□</td>";
		html += "</tr>";

	//カレンダーの曜日行
	html += "<tr>";
	for( var i = 0; i < weekArray.length; i++){
		html += "<th>";
		html += weekArray[i];
	   	html += "</th>";
	}
	html += "</tr>";

	//カレンダーの日付
	for(var i = 0; i < calLine; i++){
		html += "<tr>";
		for(var j = 0; j < 7; j++){
			var date = ( calArray[ j + ( i * 7 )] != undefined ) ? calArray[ j + ( i * 7 )] : "";
			html += "<td" + getDateClass( date, j ) + ">";
			html += getDateTag( date, j );
			html += "</td>";
		}
		html += "</tr>";
	}
	html += "</table>";

	//説明文
	html += getHolidayTitle();
	html += getEventdayTitle();

	return html;
}

function getHolidayTitle(){
	if ( currentSetting.options.holiday_title != ""){
		return "<p><span class='boxholiday'></span>" + currentSetting.options.holiday_title + "</p>";
	}
	return "";
}

function getEventdayTitle(){

	if ( currentSetting.options.eventday_title == ""){
		return "";
	}

	var tag = "<p><span class='boxeventday'></span>"

	if ( currentSetting.options.eventday_url == "" ){
		tag += currentSetting.options.eventday_title + "</p>";
		return tag;
	}

	tag += "<a href='" + currentSetting.options.eventday_url + "'>" + currentSetting.options.eventday_title + "</a></p>";
	return tag;
}

var getDateClass = function( date, day ){

	if ( date == undefined || date == "" ){
		return "";
	}
	var today = isToday(date);
	var attr = "";

	switch( getDateType(date, day)){
	case "EVENTDAY":
		attr = today == false ? " class='eventday' " : " class='eventday today' ";
		return attr;
	case "HOLIDAY":
		attr = today == false ? " class='holiday' " : " class='holiday today' ";
		return attr;
	default:
		attr = today == false ? "" : " class='today' ";
		return attr;
	}
	return "";
}

function isToday( date ){
	var now = new Date();
	if ( now.getFullYear() == currentSetting.year && now.getMonth() + 1 == currentSetting.month && now.getDate() == date){
		return true;
	}
	return false;
}

var getDateTag = function( date, day ){

	if ( date == undefined || date == "" ){
		return "";
	}

	var url = currentSetting.options.eventday_url;
	if ( url == ""){
		return date;
	}
	if ( getDateType(date, day) == "EVENTDAY"){
		return tag = "<a href='" + url + "'>" + date + "</a>";
	}

	return date;
}

var getDateType = function( date, day ){

	var fulldate = getFormatDate( currentSetting.year, currentSetting.month, date);

	//イベント日
	if ( currentSetting.options.eventdays.indexOf(fulldate) != -1){
		return "EVENTDAY";
	}

	//臨時営業日
	if ( currentSetting.options.temp_weekdays.indexOf(fulldate) != -1){
		return "WEEKDAY";
	}

	//臨時休業日
	if ( currentSetting.options.temp_holidays.indexOf(fulldate) != -1){
		return "HOLIDAY";
	}

	//定休日
	var dayName = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
	if ( currentSetting.options[ dayName[day]] == "on" ){
		return "HOLIDAY";
	}

	//祝日
	if ( isHoliday(fulldate) ){
		return "HOLIDAY";
	}
	return "WEEKDAY";
}

var isHoliday = function( fulldate ){
	if ( currentSetting.options[ "holiday" ] == "on" && currentSetting.holidays[fulldate] != null ){
		return true;
	}
	return false;
}

var getFormatDate = function( y, m, d){
	m = m < 10? "0" + m : m;
	d = d < 10? "0" + d : d;
	return y + "-" + m + "-" + d;
}

var getEndDateOfMonth = function (year, month){
    var date = new Date(year, month, 0);
    return date.getDate();
}


var getStartDayOfMonth = function (year, month){
    var date = new Date(year, month -1, 1);
    return date.getDay();
}