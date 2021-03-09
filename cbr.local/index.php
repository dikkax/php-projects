<?php 
    const FILE_NAME = "valutes.xml"; //codes for GetCursDynamic
    const TLL = 86400; //24 hours

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        try {
            $client = new SoapClient("https://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL");
            
            //----------- GetCursOnDate  -----------
            if (!empty($_POST["date"])){
                $param["On_date"] = $_POST["date"];
                $result = $client->GetCursOnDateXML($param);
                $resxml = $result->GetCursOnDateXMLResult->any;
                $sxml = simplexml_load_string($resxml);
                $rdate = substr($sxml["OnDate"], 6, 2) . "-" . substr($sxml["OnDate"], 4, 2) . "-" . substr($sxml["OnDate"], 0, 4);
            }
            
            //----------- GetCursDynamic  -----------
            if (!empty($_POST["valute"]) and !empty($_POST["from_date"]) and !empty($_POST["to_date"]))
            {
                if (!is_file(FILE_NAME) or (time() > (filemtime(FILE_NAME) + TLL))){ 
                    $param2["Seld"] = false;
                    $result2 = $client->EnumValutesXML($param2);
                    $resxml2 = $result2->EnumValutesXMLResult->any;
                    file_put_contents(FILE_NAME, $resxml2);
                }
                
                $sxml2 = simplexml_load_file(FILE_NAME);
                $param3["FromDate"] = $_POST["from_date"];
                $param3["ToDate"] = $_POST["to_date"];
                foreach($sxml2->EnumValutes as $valute){
                    if ($valute->VcharCode == $_POST["valute"]){
                        $param3["ValutaCode"] = trim($valute->Vcode);
                        break;
                    }
                }
                $result3 = $client->GetCursDynamicXML($param3);
                $resxml3 = $result3->GetCursDynamicXMLResult->any;
                file_put_contents("last_dyn_res.xml", $resxml3);
                $sxml3 = simplexml_load_string($resxml3);
            }
        } catch (SoapFault $exception) {
            $err = $exception->getMessage();	
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Курс валют</title>
</head>
<body>
    <h1>Курс валюты <?= (!empty($rdate))?"на $rdate:":"";?></h1>
    <form action="" method="post">
        <div>
            <label>Выберите валюту </label>
            <select name="valute">
            <option value=""></option>
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
            <option value="AUD">AUD</option>
            <option value="AZN">AZN</option>
            <option value="GBP">GBP</option>
            <option value="AMD">AMD</option>
			<option value="BYN">BYN</option>
            <option value="BGN">BGN</option>
            <option value="BRL">BRL</option>
            <option value="HUF">HUF</option>
			<option value="HKD">HKD</option>
            <option value="DKK">DKK</option>
			<option value="INR">INR</option>
            <option value="KZT">KZT</option>
            <option value="CAD">CAD</option>
            <option value="KGS">KGS</option>
			<option value="CNY">CNY</option>
            <option value="MDL">MDL</option>
            <option value="NOK">NOK</option>
            <option value="PLN">PLN</option>
			<option value="RON">RON</option>
            <option value="XDR">XDR</option>
            <option value="SGD">SGD</option>
            <option value="TJS">TJS</option>
			<option value="TRY">TRY</option>
            <option value="TMT">TMT</option>
            <option value="UZS">UZS</option>
            <option value="UAH">UAH</option>
			<option value="CZK">CZK</option>
            <option value="SEK">SEK</option>
            <option value="CHF">CHF</option>
            <option value="ZAR">ZAR</option>
			<option value="KRW">KRW</option>
            <option value="JPY">JPY</option>
            </select>
        </div>
        <div>
            <label>Выберите дату </label>
            <input type="date" name="date" >
        </div>
        <div>
            <label>(или период) </label>
            <input type="date" name="from_date" ><label> - </label><input type="date" name="to_date" >
        </div>
        <input type="submit" value="Получить">
    </form>
<?php 
    if(!empty($sxml)){
        foreach ($sxml->ValuteCursOnDate as $valute){
            if(!empty($_POST["valute"])){
                if ($_POST["valute"] == $valute->VchCode){
                    echo "<h3>$valute->Vname ($valute->VchCode): $valute->Vcurs</h3>";
                    break;
                }
            }
            else {
                echo "<h3>$valute->Vname ($valute->VchCode): $valute->Vcurs</h3>";
            }
        }
    }
    else if(!empty($sxml3)){
       $from = substr($sxml3->ValuteCursDynamic[0]->CursDate, 0, 10);
       $d1 = substr($from, 8, 2) . "-" . substr($from, 5, 2) . "-" . substr($from, 0, 4);
       $last = $sxml3->ValuteCursDynamic->count() - 1;
       $to =  substr($sxml3->ValuteCursDynamic[$last]->CursDate, 0, 10);
       $d2 = substr($to, 8, 2) . "-" . substr($to, 5, 2) . "-" . substr($to, 0, 4);
       
       echo "<h3>Динамика {$_POST["valute"]} c $d1 до $d2:</h3>";
       echo "<img src='create_image.php'>";
       echo "<br><a href='last_dyn_res.xml'>Raw XML Data</a>";
    }
    else {
        echo "<h3>$err</h3>";
    } 
?>
</body>
</html>