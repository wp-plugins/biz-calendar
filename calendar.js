jQuery(document).ready(function($) {
	if ($("#biz_calendar").size() == 0) {
		return;
	}
	bizCalendar();
});

var currentSetting = null;

var bizCalendar = function() {
	var options = window.bizcalOptions;
	var now = new Date();

	var setting = {
		year : now.getFullYear(),
		month : now.getMonth() + 1,
		options : options
	};
	window.currentSetting = setting;
	document.getElementById('biz_calendar').innerHTML = getCalendar(setting);
}

function downMonth() {
	if (currentSetting.month == 1) {
		currentSetting.month = 12;
		currentSetting.year = currentSetting.year - 1;
	} else {
		currentSetting.month = currentSetting.month - 1;
	}
	document.getElementById('biz_calendar').innerHTML = getCalendar(currentSetting);
}

function upMonth() {
	if (currentSetting.month == 12) {
		currentSetting.month = 1;
		currentSetting.year = currentSetting.year + 1;
	} else {
		currentSetting.month = currentSetting.month + 1;
	}
	document.getElementById('biz_calendar').innerHTML = getCalendar(currentSetting);
}

function goToday() {
	var now = new Date();
	if ( currentSetting.month == now.getMonth() + 1 && currentSetting.year == now.getFullYear()){
		return;
	}
	currentSetting.year = now.getFullYear();
	currentSetting.month = now.getMonth() + 1;
	document.getElementById('biz_calendar').innerHTML = getCalendar(currentSetting);
}

var getCalendar = function(setting) {
	var weekArray = new Array("日", "月", "火", "水", "木", "金", "土");
	var start_day = getStartDayOfMonth(setting.year, setting.month);
	var last_date = getEndDateOfMonth(setting.year, setting.month);
	var calLine = Math.ceil((start_day + last_date) / 7);
	var calArray = new Array(7 * calLine);

	// カレンダーの日付テーブル作成
	for ( var i = 0; i < 7 * calLine; i++) {
		if (i >= last_date) {
			break;
		}
		calArray[i + start_day] = i + 1;
	}

	// カレンダーのタイトル
	var title = setting.year + "年 " + setting.month + "月";
	var html = "<table class='bizcal' ><tr>";
	html += "<td class='calmonth' colspan='4'>" + title + "</td>";
	html += getPrevMonthTag();
	html += "<td class='calbtn today-img' onclick='goToday()' title='今月へ' ><img src='" + bizcalplugindir + "image/today.png' ></td>";
	html += getNextMonthTag();
	html += "</tr>";

	// カレンダーの曜日行
	html += "<tr>";
	for ( var i = 0; i < weekArray.length; i++) {
		html += "<th>";
		html += weekArray[i];
		html += "</th>";
	}
	html += "</tr>";

	// カレンダーの日付
	for ( var i = 0; i < calLine; i++) {
		html += "<tr>";
		for ( var j = 0; j < 7; j++) {
			var date = (calArray[j + (i * 7)] != undefined) ? calArray[j
					+ (i * 7)] : "";
			html += "<td" + getDateClass(date, j) + ">";
			html += getDateTag(date, j);
			html += "</td>";
		}
		html += "</tr>";
	}
	html += "</table>";

	// 説明文
	html += getHolidayTitle();
	html += getEventdayTitle();
	return html;
}

function getHolidayTitle() {
	if (currentSetting.options.holiday_title != "") {
		return "<p><span class='boxholiday'></span>"
				+ currentSetting.options.holiday_title + "</p>";
	}
	return "";
}

function getEventdayTitle() {

	if (currentSetting.options.eventday_title == "") {
		return "";
	}

	var tag = "<p><span class='boxeventday'></span>"

	if (currentSetting.options.eventday_url == "") {
		tag += currentSetting.options.eventday_title + "</p>";
		return tag;
	}

	tag += "<a href='" + currentSetting.options.eventday_url + "'>"
			+ currentSetting.options.eventday_title + "</a></p>";
	return tag;
}

var getDateClass = function(date, day) {

	if (date == undefined || date == "") {
		return "";
	}
	var today = isToday(date);
	var attr = "";

	switch (getDateType(date, day)) {
	case "EVENTDAY":
		attr = today == false ? " class='eventday' "
				: " class='eventday today' ";
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

function isToday(date) {
	var now = new Date();
	if (now.getFullYear() == currentSetting.year
			&& now.getMonth() + 1 == currentSetting.month
			&& now.getDate() == date) {
		return true;
	}
	return false;
}

var getDateTag = function(date, day) {

	if (date == undefined || date == "") {
		return "";
	}

	var url = currentSetting.options.eventday_url;
	if (url == "") {
		return date;
	}
	if (getDateType(date, day) == "EVENTDAY") {
		return tag = "<a href='" + url + "'>" + date + "</a>";
	}

	return date;
}

var getDateType = function(date, day) {

	var fulldate = getFormatDate(currentSetting.year, currentSetting.month,
			date);

	// イベント日
	if (currentSetting.options.eventdays.indexOf(fulldate) != -1) {
		return "EVENTDAY";
	}

	// 臨時営業日
	if (currentSetting.options.temp_weekdays.indexOf(fulldate) != -1) {
		return "WEEKDAY";
	}

	// 臨時休業日
	if (currentSetting.options.temp_holidays.indexOf(fulldate) != -1) {
		return "HOLIDAY";
	}

	// 定休日
	var dayName = [ "sun", "mon", "tue", "wed", "thu", "fri", "sat" ];
	if (currentSetting.options[dayName[day]] == "on") {
		return "HOLIDAY";
	}

	return "WEEKDAY";
}

var getFormatDate = function(y, m, d) {
	m = m < 10 ? "0" + m : m;
	d = d < 10 ? "0" + d : d;
	return y + "-" + m + "-" + d;
}

var getEndDateOfMonth = function(year, month) {
	var date = new Date(year, month, 0);
	return date.getDate();
}

var getStartDayOfMonth = function(year, month) {
	var date = new Date(year, month - 1, 1);
	return date.getDay();
}

var getPrevMonthTag = function() {
	var limit = currentSetting.options["month_limit"];
	var tag = "<td class='calbtn down-img' onclick='downMonth()' title='前の月へ' ><img src='" + bizcalplugindir + "image/down.png' ></td>";
	if (limit == undefined || limit == "制限なし") {
		return tag;
	}
	var can_move = true;
	var now = new Date();
	var now_year =  now.getFullYear();
	var now_month = now.getMonth()  + 1;

	if (limit == "年内") {
		if (currentSetting.month == 1) {
			can_move = false;
		}
	} else if (limit == "年度内") {
		if (currentSetting.month == 4) {
			can_move = false;
		}
	} else {
		var prev_limit = currentSetting.options["prevmonthlimit"] == undefined ? 0 : currentSetting.options["prevmonthlimit"];
		var prev_limit_year = now_year;
		var prev_limit_month = now_month - Number(prev_limit);
		if ( prev_limit_month < 1){
			prev_limit_year -= 1;
			prev_limit_month += 12;
		}
		if (currentSetting.month == prev_limit_month && currentSetting.year == prev_limit_year) {
			can_move = false;
		}
	}

	if (!can_move) {
		tag = "<td class='calbtn down-img' ><img src='" + bizcalplugindir + "image/down-limit.png' ></td>";
	}
	return tag;
}

var getNextMonthTag = function() {
	var limit = currentSetting.options["month_limit"];
	var tag = "<td class='calbtn up-img' onclick='upMonth()' title='次の月へ' ><img src='" + bizcalplugindir + "image/up.png' ></td>";
	if (limit == undefined || limit == "制限なし") {
		return tag;
	}
	var can_move = true;
	var now = new Date();
	var now_year = now.getFullYear();
	var now_month = now.getMonth() + 1;

	if (limit == "年内") {
		if (currentSetting.month == 12) {
			can_move = false;
		}
	} else if (limit == "年度内") {
		if (currentSetting.month == 3) {
			can_move = false;
		}
	} else {
		var next_limit = currentSetting.options["nextmonthlimit"] == undefined ? 0 : currentSetting.options["nextmonthlimit"];
		var next_limit_year = now_year;
		var next_limit_month = now_month + Number(next_limit);
		if ( next_limit_month > 12){
			next_limit_year += 1;
			next_limit_month -= 12;
		}
		if (currentSetting.month == next_limit_month && currentSetting.year == next_limit_year) {
			can_move = false;
		}
	}

	if ( !can_move) {
		tag = "<td class='calbtn up-img' ><img src='" + bizcalplugindir + "image/up-limit.png' ></td>";
	}
	return tag;
}