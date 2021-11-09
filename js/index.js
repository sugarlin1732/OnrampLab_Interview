$(document).ready(function(){
    // 刷新頁面顯示分類選項1
    $.ajax({
        url: "./action.php",
        method: "POST",
        async: false,
        cache: false,
        data: "Action=ShowFilter",
        success: function(msg){
            // console.log(msg);
            $("#from_here").html(msg);
        }
    });


    // 選擇分類選項1後顯示分類選項2
    $(document).on("change" ,"#category_sel1", function(){
        category1 = $("#category_sel1").val();
        // console.log(category1);

        $.ajax({
            url: "./action.php",
            method: "POST",
            async: false,
            cache: false,
            data: "Action=Sel_Category2&Category1=" + category1,
            success: function(msg){
                // console.log(msg);
                $("#category_sel2").html(msg);
            }
        });
    });


    // 選擇分類2後顯示看板選項
    $(document).on("change" ,"#category_sel2", function(){
        category2 = $("#category_sel2").val();
        // console.log(category2);

        $.ajax({
            url: "./action.php",
            method: "POST",
            async: false,
            cache: false,
            data: "Action=Sel_Board&Category2=" + category2,
            success: function(msg){
                // console.log(msg);
                $("#Board_sel").html(msg);
            }
        });
    });


    // submit表單
    $(document).on("submit", "#filter_form", function(){
        var formData = new FormData($("#filter_form")[0]);
        // for (var pair of formData.entries()) {
        //     console.log(pair[0]+ ', ' + pair[1]); 
        // }

        date_start = $("#Date_start").val();
        date_end = $("#Date_end").val();

        if ((date_start == "" && date_end != "") || (date_start != "" && date_end == "")){
            alert("請選擇日期");
            return false;
        }

        $.ajax({
            url: "./action.php",
            method: "POST",
            data: formData,
            async: false,
            cache: false,
            processData: false,
            contentType: false,
            success: function(msg){
                console.log(msg);
                $("#result").html(msg);
            }
        });
        return false;
    });

})