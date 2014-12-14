<?php
$password = "201345";

class core
{
    public $method, $data,
            $firefix_version = "8",
            $joomla_version;
    
    public function out($method,$data=""){
        $this->method = $method;
        $this->data = $data;
        return $this->$method();
    }
    
    #**********[ FUNCTIONS ]**************
    function filelist($dir,$separator,$filesize=FALSE){
        function GetDirFilesR($dir){   
            $dir_iterator = new RecursiveDirectoryIterator($dir);
            $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
            return $iterator;
            }
            $scaner = GetDirFilesR($dir);
            foreach($scaner as $v){
            if($v->isFile()){
                if($filesize===TRUE){                   
                    $fs = "|".$this->getFileSize($v);                   
                }
                $v = substr($v, 2);
                $return .= $v.$fs.$separator;
                }
            }
            
                $faa = fopen("joomla3.txt", "w");
                fwrite($faa, $return);
                fclose($faa);
            
            return $return;
    }
    
    function getFileSize($filename){
        if(file_exists($filename)){
        $fs = file_get_contents($filename);
        $pattern = "/[a-z0-9]/";
        preg_match_all($pattern,$fs,$outfs);
        return count($outfs[0]);
        } else {
            return 0;
        }
    }
    
    function getJoomlaVersion(){
        if(file_exists("libraries/cms/version/version.php")){
        $verfile = file_get_contents("libraries/cms/version/version.php");
        $find_in = preg_quote('public $RELEASE = \'');
        $find_to = preg_quote("';");
        $pattern = "/$find_in(.+?)$find_to/";
        $find_in2 = preg_quote('public $DEV_LEVEL = \'');
        $pattern2 = "/$find_in2(.+?)$find_to/";
        preg_match_all($pattern, $verfile, $version);
        preg_match_all($pattern2, $verfile, $revision);
        
        return $version[1][0].".".$revision[1][0];
        
        } else {
            return "1.5";
        }
    }
    
    function checkUpdate(){
        $lastversion = file_get_contents("http://mraak.ru/get_firefix_version.php");
        
        if($this->firefix_version < $lastversion){
            return "<span style='color:red;margin-left:20px'> Ваша версия firefix устарела!</span> <span id='update'>Обновить</span>";
        } else {
            return "<span style='color:green;margin-left:20px'> Версия: ".$this->firefix_version."</span>";
        }
    }


    #************[ PAGES ]****************
    public function update(){        
       
       if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, "http://mraak.ru/firefix.php.txt");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $lastversion = curl_exec($curl);
            curl_close($curl);
       }
       
       
       
       
       if($lastversion == ""){
           return "Update error";
       } else {
       $faa = fopen("firefix.php", "w");
              fwrite($faa, $lastversion);
              fclose($faa);
       return "firefix обновлён!";
       }
    }
    public function loginwin(){        
        return "<input id='pass' type='password'></input><button id='login'>Войти</button>";      
    }
    public function mainpage(){
        $getupdate = $this->checkUpdate();
        $content = "<div class='box'><button id='scan'>Сканировать</button> $getupdate</div>";
        return $content;
    }
    public function openfile(){
        $gethtml = file_get_contents($this->data);
        $out = "<style>#codeblock{width: 935px;height: 500px;font-size: 12px;}</style>";
        $out .="<textarea id='codeblock'>$gethtml</textarea>";
        return $out;
    }
    public function delfiles(){
        $files = explode('■',$this->data);
        $n = count($files);
        $out = "Файлов на удаление $n<br/><br/>";
        for($i=0;$i<$n;$i++){
            if(file_exists($files[$i])){
                unlink($files[$i]);
                $out .= "$i. Файл: ".$files[$i]." <span style='color:red'>Удалён</span><br/>";
            } else {
                $out .= "<span style='color:red'>$i. ОШИБКА! файл ".$files[$i]." не существует...</span><br/>";
            }
        }       
        return $out;
    }
    
    public function scan(){
        set_time_limit(0);
        $ai_bolit = "http://".$_SERVER['HTTP_HOST']."/ai-bolit.php?p=broster201345";
        
        
        $reportname = $_SERVER['HTTP_HOST']."_ai-bolit-report.html";
        
        if(file_exists($reportname)){
            $ai_bolit_out = file_get_contents($reportname);
            $report_on_disk = " Файл отчёта антивируса найден";
        } else {
            
            if(!file_exists("ai-bolit.php")){
                
                if( $curl = curl_init() ) {
                    curl_setopt($curl, CURLOPT_URL, "http://mraak.ru/ai-bolit.php.txt");
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                    $aibolit = curl_exec($curl);
                    curl_close($curl);
                }
                
                
                
                $faa = fopen("ai-bolit.php", "w");
                fwrite($faa, $aibolit);
                fclose($faa);
            }
            
            
        
        if( $curl = curl_init() ) {
            curl_setopt($curl, CURLOPT_URL, $ai_bolit);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            $ai_bolit_out = curl_exec($curl);
            curl_close($curl);
          }
        
        $faa = fopen($reportname, "w");
        fwrite($faa, $ai_bolit_out);
        fclose($faa);
        }
             
        $this->joomla_version = $this->getJoomlaVersion();
        
        
        $tag = "a";
        $attr = "class";
        $value = "it";
        $tag = preg_quote($tag);
        $attr = preg_quote($attr);
        $value = preg_quote($value);
        $pattern = "/<(".$tag.")[^>]*$attr\s*=\s*(['\"])$value\\2[^*>]*>(.*?)<\/\\1>/";
        preg_match_all($pattern, $ai_bolit_out, $outreport_line_1);
        $pattern = "/L(\d+) .../";
        preg_match_all($pattern, $ai_bolit_out, $outreport_L);
        
        $codes = array(
            "1" => "red", // Удалить
            "2" => "red", // Удалить
            "4" => "red", // Удалить
            "8" => "red", // Удалить

            "26" => "#03FF2C", // ложное срабатывание
            "56" => "#03FF2C", // ложное срабатывание
            "72" => "#03FF2C", // ложное срабатывание
            "76" => "#03FF2C", // ложное срабатывание
            "110" => "#03FF2C", // ложное срабатывание
            "118" => "#03FF2C", // ложное срабатывание
            "119" => "#03FF2C", // ложное срабатывание
            "114" => "#03FF2C", // ложное срабатывание
            "124" => "#03FF2C", // ложное срабатывание
            "133" => "#03FF2C", // ложное срабатывание
            "134" => "#03FF2C", // ложное срабатывание
            "428" => "#03FF2C", // ложное срабатывание
            "328" => "#03FF2C", // ложное срабатывание
            "1280" => "#03FF2C", // ложное срабатывание
            "1048" => "#03FF2C", // ложное срабатывание
            "6636" => "#03FF2C", // ложное срабатывание
        );
        
        $duble_check = array();
        $duble_check[] = "firefix.php";
        
        
        // определяем массив версии
            $jver = substr($this->joomla_version, 0, 1);
            
            if(file_exists("j$jver.txt")){
               $file_table = file_get_contents("j$jver.txt");
               $report_on_disk .= " [Локальная хэштаблица]";
            } else {
                if( $curl = curl_init() ) {
                curl_setopt($curl, CURLOPT_URL, "http://mraak.ru/j$jver.txt");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                $file_table = curl_exec($curl);
                curl_close($curl);
                }
            }
            
        // подгружаем хэштаблицу
        $jf_array = explode(";", $file_table); // Массив файлов дистрибутива

        $joomla_files = array();
        $joomla_bytes = array();

        foreach ($jf_array as $f){
            $vv = explode("|",$f);
            $joomla_files[] = $vv[0];
            $joomla_bytes[] = $vv[1];
        }
        
        // основной цыкл
        for($i=0;$i<count($outreport_line_1[0]);$i++){
    
            if($outreport_line_1[0][$i]!=$outreport_line_1[0][$i+1] && $outreport_line_1[0][$i+1]!=""){
            $link = $outreport_line_1[0][$i];
            $link = strip_tags($link,"");
            $pos_slash = strpos($link, "/");
            $link = substr($link, $pos_slash+1);
            $warning_num = $outreport_L[1][$i];
            $color = $codes[$warning_num];
            
            ######## Сравнение файлов
            $key_file = array_search($link, $joomla_files);
            if($key_file!=FALSE){
                $joomla_file = "Y";
                $f_color = "#03FF2C";
                $joom_byte = $joomla_bytes[$key_file]; // Размер файла если он есть в дистре               
                
                $thisfilesize = $this->getFileSize($link);
                
                if($joom_byte == $thisfilesize){
                    $ratio = "=";
                    $color = "green";
                } else {
                    if($thisfilesize!=0){
                    $ratio = "<>";
                    $color = "red";
                    } else {
                    $ratio = "0";
                    $color = "grey"; 
                    }
                }
                
                $diff = "<td style='font-size:12px;font-weight: bold;text-align:center;color:$color;'>$ratio</td>";
            } else {
                $joomla_file = "N";
                $f_color = "red";
                $ratio="";
                $ratio_color = "#3253FF";
                $diff = "<td></td>";
            }


            $check = "false";

            if($joomla_file=="N" && $color=="red"){
                $check = "true";
                $check_back = " style='background:rgb(255, 158, 158)'";
            } else {
                $check_back = "";
            }
                       
            // Проверка на дубликат
            $duble_verify = array_search($link, $duble_check);
            if($duble_verify===FALSE){
            $tr_list .= "<tr><td class='open_i' path='$link'>$i</td><td style='background:$color'>$warning_num</td><td style='background:$f_color'>$joomla_file</td>$diff<td class='link' chk='$check'$check_back>$link</td></tr>";
            $duble_check[] = $link;
                }
            }
        }
        
        $content = "<div class='box'>Результаты сканирования</div>";
        $content .= '<div class="box"><a target="windowName" onclick="window.open(this.href,this.target,\'width=800,height=600\');return false;" href="'.$reportname.'">Просмотреть отчёт Aibolit</a></div>';
        $content .= "<div class='box'>Версия Joomla $this->joomla_version$report_on_disk</div>";        
        $content .="<div class='box'><table id='fixlinks' class='table'><tbody>$tr_list</tbody></table></div>";
        $content .="<div class='box'><button id='delfiles'>Удалить файлы</button></div>";
        
        
        return $content;
    }
}

$method = $_POST['method'];
$data = $_POST['data'];
$access = $_POST['access'];


if($access!=""){
    
    $render = new core;
    
    if($access!=$password){
        if($method=="login"){
            if($data==$password){
                echo "<access key='$password'/><script>loadWorkspace();</script>";
            } else {
                echo "<div id='loginin'><span style='color:red'>Неверный пароль!</span><br/>".$render->out("loginwin")."</div>";
            }
        } else {
            echo "<div id='loginin'>".$render->out("loginwin")."</div>";
        }
    } else {
        if($method==""){$method="mainpage";}
        echo "<access key='$password'/>".$render->out($method,$data);
    }
    
}

if(isset($_GET['ajax'])){die();}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>AUDIT TOOL</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="" />
       <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    </head>
    <style type="text/css">
        body, html {
        height: 100%;
        }
        body{
            background: #F5FAFF;
        }
        #loginin{
            padding-top: 20%;
            text-align: center;
        }
        #pass{
            border: 1px solid #E9E9E9;
            text-align: center;
        }
        .box{
            width: 1000px;
            margin: 10px auto;
            background: #fff;
            padding: 20px;
            text-align: center;
        }
        .listbox{
            width: 1000px;
            margin: 10px auto;
            background: #fff;
            padding: 20px;
        }
        #fixlinks td{
            padding: 3px;
            font-size: 12px;
        }
        .link{
            text-align: left;
            cursor: pointer;
        }
        .link:hover{
            background: #89BDD7;
        }
        #alert{
            padding: 10px;
        }
        .open_i{
            text-decoration: underline;
            color: #292;
            cursor: pointer;
        }
        #update{
            color: #0000ff;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
    
    <body>
        <div id="workspace">
            
        </div>
        <div id="preloader" style="display:none">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve" width="30" height="30">

		<rect fill="#FBBA44" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;15,0;15,15;0,15;0,0;" repeatCount="indefinite"/>
		</rect>	

		<rect x="15" fill="#E84150" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;0,15;-15,15;-15,0;0,0;" repeatCount="indefinite"/>
		</rect>	
      
		<rect x="15" y="15" fill="#62B87B" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;-15,0;-15,-15;0,-15;0,0;" repeatCount="indefinite"/>
		</rect>	

		<rect y="15" fill="#2F6FB6" width="15" height="15">
      <animateTransform attributeName="transform" attributeType="XML" type="translate" dur="1.7s" values="0,0;0,-15;15,-15;15,0;0,0;" repeatCount="indefinite"/>
		</rect>
    </svg>
  </div>
        
        <!--МОДАЛЬНОЕ ОКНО-->
<div class="modal fade" id="main-modal">
  <div class="modal-dialog" style="width: 800px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button style="display: none" type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button id="save" type="button" class="btn btn-primary" data-dismiss="modal">Сохранить</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
        
        
    </body>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

<script type="text/javascript">
function ajax(ajaxdata)
{
    var out;
    $.ajax({
        type: "POST",
        url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?ajax=ajax",  
        cache: false,
        data: ajaxdata,
        async: false,
        success: function(x){
            out = x;
            }  
    });//.ajax
    return out;
}

function preloader(obj,text){
    var pre_html = $("#preloader").html();
    $(obj).parent().html("<div class='pre'>"+pre_html+"</div> "+text+".");
}

function loadWorkspace(method,data){
     var key = $("access").attr("key");
     if(key===undefined){key="guest";}
     var x = ajax({'access':key,'method':method,'data':data});
     $("#workspace").html("");
     $("#workspace").append(x);
}

loadWorkspace();

$(document).on("click", "#login", function(){
    var pass = $("#pass").val();
    loadWorkspace("login",pass);
    });

$(document).on("click", "#scan", function(){
    preloader($(this),"Сканирую");
    var key = $("access").attr("key");
    if(key===undefined){key="guest";}
    $.ajax({
        type: "POST",
        url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>?ajax=ajax",  
        cache: false,
        data: {'access':key,'method':'scan'},
        success: function(x){
            $("#workspace").html("");
            $("#workspace").append(x);
            }  
    });//.ajax
});

$(document).on("click", ".link", function(){
    var c = $(this).attr("chk");
    if(c==="false"){
        $(this).attr("chk","true").css("background","rgb(255, 158, 158)");
    } else {
        $(this).attr("chk","false").css("background","#fff");
    }
});

$(document).on("click", ".open_i", function(){
    var openfile = $(this).attr("path");
    var key = $("access").attr("key");if(key===undefined){key="guest";}
    
    $("#main-modal .modal-title").text("Просмотр кода");
    $("#main-modal .modal-dialog").css("width","1000px");
    $("#main-modal #save").text("Закрыть");
    var x = ajax({'access':key,'method':'openfile','data':openfile});
    $("#main-modal .modal-body").html(x);
    $('#main-modal').modal('show');
});


$(document).on("click", "#delfiles", function(){
    var links_cnt = $("[chk=true]").size();
    var links = [];
    for(var i=0;i<links_cnt;i++){
        links[i] = $("[chk=true]").eq(i).text();
       // links[i] = bredHandler(links[i]);
    }
    links = links.join('■');
    
    var key = $("access").attr("key");if(key===undefined){key="guest";}
    //console.log(links);
    var x = ajax({'access':key,'method':'delfiles','data':links});
    $("#workspace").append("<div class='box' style='text-align:left'>"+x+"</div>");
});


$(document).on("click", "#update", function(){
    var key = $("access").attr("key");if(key===undefined){key="guest";}
    var x = ajax({'access':key,'method':'update'});
    //$("#workspace").append("<div class='box' style='text-align:left'>"+x+"</div>");
    location.reload();
});

 
</script>

</html>