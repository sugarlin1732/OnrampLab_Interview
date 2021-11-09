<?php
require_once("./simple_html_dom.php");

$Action             = isset($_POST["Action"])               ? $_POST["Action"]              : "";
$Category1          = isset($_POST["Category1"])            ? $_POST["Category1"]           : "";
$Category2          = isset($_POST["Category2"])            ? $_POST["Category2"]           : "";
$Board              = isset($_POST["Board"])                ? $_POST["Board"]               : "";
$Number             = isset($_POST["Number"])               ? $_POST["Number"]              : "";
$Title_search       = isset($_POST["Title_search"])         ? $_POST["Title_search"]        : "";
$Author_search       = isset($_POST["Author_search"])         ? $_POST["Author_search"]        : "";
$Date_start         = isset($_POST["Date_start"])           ? $_POST["Date_start"]          : "";
$Date_end           = isset($_POST["Date_end"])             ? $_POST["Date_end"]            : "";


// print_r($_POST);
switch($Action){
    case "ShowFilter";
        Show_Filter();
        break;

    case "Sel_Category2";
        Sel_Category2($Category1);
        break;

    case "Sel_Board";
        Sel_Board($Category2);
        break;

    case "Search":
        Show_Result($Board, $Number ,$Title_search, $Author_search, $Date_start, $Date_end);
        break;

    default:
        break;
}


function Show_Filter(){
    $text = "<form id='filter_form'>";
    $text .= "選擇分類1&nbsp<select id='category_sel1' name='Category1' required>";

    $text .= "<option value='' selected disabled>------------請選擇分類------------</option>";
    $html = file_get_html("https://www.pttweb.cc/cls/1");
    $category_list = $html->find("ul.e7-ul.e7-traditional", 0);
    foreach ($category_list->find("a") as $value){
        $category = ltrim($value->plaintext);
        $link = $value->href;
        $text .= "<option value='".$link."'>".$category."</option>";
    }
    $text .= "</select>&nbsp&nbsp";

    $text .= "選擇分類2&nbsp<select id='category_sel2' name='Category2' required></select>&nbsp&nbsp";
    $text .= "選擇看板&nbsp<select id='Board_sel' name='Board' required></select>&nbsp&nbsp";
    
    // 來不及做成每頁顯示數量
    $text .= "搜尋筆數&nbsp<select id='Number_sel' name='Number' required>";
    $text .= "<option value='' selected disabled>請選擇數量</option>";
    $text .= "<option value='1'>1</option>";
    $text .= "<option value='10'>10</option>";
    $text .= "<option value='20'>20</option>";
    $text .= "<option value='50'>50</option>";
    $text .= "<option value='100'>100</option>";
    $text .="</select>&nbsp&nbsp";
    
    // filter
    $text .= "<br>搜尋標題&nbsp<input type='text' id='Title_search' name='Title_search'>&nbsp&nbsp";
    $text .= "搜尋作者&nbsp<input type='text' id='Author_search' name='Author_search'>&nbsp&nbsp";
    // $text .= "搜尋內文&nbsp<input type='text' id='Abstract_search' name='Abstract_search'>&nbsp&nbsp"; 應該會搜尋到天荒地老
    $text .= "文章時間: 起&nbsp<input type='date' id='Date_start' name='Date_start'>&nbsp&nbsp";
    $text .= "~迄&nbsp<input type='date' id='Date_end' name='Date_end'>&nbsp&nbsp";
    
    $text .= "<input type='text' style='display: none;' name='Action' value='Search'>";
    $text .= "<input type='submit' value='搜尋'>";
    // $html .= "<button id='form_submit'>搜尋</button>";
    $text .= "</form>";

    echo $text;
}



function Sel_Category2($Category1){
    $text = "<option value='' selected disabled>------------請選擇分類------------</option>";
    $html = file_get_html("https://www.pttweb.cc".$Category1);
    $category_div = $html->find(".e7-list-content", 0);

    foreach ($category_div->find("a.e7-list-item") as $value){
        $link = $value->href;
        $category_tmp = $value->find("div.e7-directory-name", 0);
        $category_name = "";
        foreach ($category_tmp->find("span") as $name){
            $category_name .= $name->plaintext;
        }
        $text .= "<option value='".$link."'>".$category_name."</option>";
    }
    echo $text;
}

function Sel_Board($Category2){
    $text = "<option value='' selected disabled>------------請選擇看板------------</option>";
    $html = file_get_html("https://www.pttweb.cc".$Category2);
    $board_div = $html->find(".e7-list-content", 1);

    foreach ($board_div->find("a.e7-list-item") as $value){
        $link = $value->href;
        $board_tmp = $value->find("div.e7-board-name", 0);
        $board_name = $board_tmp->plaintext;
        $text .= "<option value='".$link."'>".$board_name."</option>";
    }
    echo $text;
}



function Show_Result($Board, $Number ,$Title_search, $Author_search, $Date_start, $Date_end){
    // 不知為啥爬ptt有夠慢...
    set_time_limit(60);

    $html = file_get_html("https://www.pttweb.cc".$Board);
    $count = 0;

    $article_list = $html->find(".mt-2", 0);
    foreach ($article_list->find(".e7-container") as $tmp){
        // 只讀取N筆
        if ($count == $Number){
            break;
        }

        // 取得文章超連結
        $link = $tmp->find("a.e7-article-default", 0);
        $link = "https://www.pttweb.cc".$link->href;

        // 標題太長會變兩行，第二行class=__e7-full-title-appended-part，接到上一行再印出
        $title_tmp = $tmp->find(".e7-show-if-device-is-not-xs span");
        $title_1 = $title_tmp[0]->plaintext;
        $title_2 = $title_tmp[1]->plaintext;
        $title = $title_1.$title_2;
        if (strpos($title, $Title_search) === false) {
            continue;
        }

        // 取得作者
        $author_tmp = $tmp->find("span.grey--text.e7-link-to-article", 0);
        $author = $author_tmp->plaintext;
        if (strpos($author, $Author_search) === false) {
            continue;
        }

        // 取得日期
        // 只抓到月日，沒抓到年和時分，要再研究
        $time_tmp = $tmp->find("span.text-no-wrap", 1);
        $time = $time_tmp->plaintext;

        // 只能先以月日篩選
        // 假設是2021
        if ($Date_start != "" && $Date_end != ""){
            $new_time = strtotime("2021-".str_replace("/", "-", $time));
            $new_date_start = strtotime($Date_start);
            $new_date_end = strtotime($Date_end);
                
            if (($new_time < $new_date_start) || ($new_time > $new_date_end)){
                continue;
            }
        }
        
        // Get description
        $article = file_get_html($link);
        $abstract_div = $article->find(".e7-main-content", 0);
        $abstract_tmp = $abstract_div->find("span", 0);
        $abstract = $abstract_tmp->plaintext;
        $abstract = mb_substr($abstract, 0, 100, "UTF-8")."...";

        echo "<a href='".$link."'><h3>".$title."</h3></a>";
        echo "<p>".$abstract."</p>";
        echo "<p>by ".$author." on ".$time."</p>";
        echo "<br>";

        $count ++;
    
    }
    
}


?>