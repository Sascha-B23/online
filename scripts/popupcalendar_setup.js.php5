
//monthName = new Array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");
gotoString = "Zum aktuellen Monat";
todayString = "Heute ist";
weekString = "KW";

startAt = 1;
dayName = new Array ("Mo","Di","Mi","Do","Fr","Sa","So");

addHoliday(1,1,0, "Neujahr");
addHoliday(6,1,0, "Hl. Drei Könige (Baden-Württemberg, Bayern, Sachsen-Anhalt)");
addHoliday(15,8,0, "Maria Himmelfahrt (nur Bayern und Saarland)");
addHoliday(3,10,0,"Nationalfeiertag");
addHoliday(31,10,0,"Reformationstag (Brandenburg, Mecklenburg-Vorpommern, Sachsen, Sachen-Anhalt, Thüringen)");
addHoliday(1,11,0,"Allerheiligen (Baden-Württenberg, Bayern, Nordrhein-Westfalen, Rheinland-Pfalz, Saarland, Thüringen)");
<? for($a=date("Y",time())-10;$a<date("Y",time())+10;$a++){?>
	addHoliday(<?=date("j,n,Y",easter_date ($a)-86401);?>, "Karfreitag");
	addHoliday(<?=date("j,n,Y",easter_date ($a));?>, "Ostersonntag");
	addHoliday(<?=date("j,n,Y",easter_date ($a)+86400);?>, "Ostermontag");
	addHoliday(<?=date("j,n,Y",easter_date ($a)+86400*39);?>, "Christi Himmelfahrt");
	addHoliday(<?=date("j,n,Y",easter_date ($a)+86400*49);?>, "Pfingstsonntag");
	addHoliday(<?=date("j,n,Y",easter_date ($a)+86400*50);?>, "Pfingstmontag");
	addHoliday(<?=date("j,n,Y",easter_date ($a)+86400*60);?>, "Fronleichnam (Baden-Württemberg, Bayern, Hessen, Nordrhein Westfalen, Rheinland-Pfalz und im Saarland)");
<? }?>
addHoliday(1,5,0,"Tag der Arbeit");
addHoliday(25,12,0, "1. Weihnachtsfeiertag");
addHoliday(26,12,0, "2. Weihnachtsfeiertag");
