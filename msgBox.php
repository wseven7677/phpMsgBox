<!--留言板 发送留言-->
<div class="row center-block">
	<div class="col-lg-12">
		<?php 
    require_once('allvars.php');//请将数据库登录数据放在这里。
//初始化提交留言要用的变量：
    $name='';
    $email='';
    $message='';

    if (isset($_POST['submit'])){
	//当用户提交表单时获得信息 & 防止xss：	
        $name = addslashes(htmlspecialchars(strip_tags(trim($_POST['name']))));
	$email = addslashes(htmlspecialchars(strip_tags(trim($_POST['email']))));
	$message = addslashes(htmlspecialchars(strip_tags(trim($_POST['cf_message']))));
		
      $time = date('Y-m-d H:i:s',time());//获得当时时间
      $output_form = false;//相当于一个标旗，方便错误提示信息的显示
	    
//判断输入是否符合要求：
      if(empty($name) && empty($email)){
        echo '称呼和电子邮箱地址不能为空。 <br /> ';
        $output_form = true;
      }

      if(empty($name) && !empty($email)){
        echo '称呼不能为空。 <br /> ';
        $output_form = true;
      }

      if(!empty($name) && empty($email)){
        echo '电子邮箱地址不能为空。 <br /> ';
        $output_form = true;
      }
	//都符合要求后，将信息存至数据库：
      if(!empty($name) && !empty($email)){
        echo '发送中... <br /> ';

        $success = 0;
        $dbc = mysqli_connect(DB_HOST,DB_USER,DB_PW,DB_NAME) or die('error connecting to mysql server');
        //查找最大id号：
        $query0 = "
          SELECT MAX(id) FROM messagebox
        ";
        $func_id = mysqli_query($dbc,$query0) or die('error querying database[id problem]');
        $numID = mysqli_fetch_array($func_id);//使用时use numID[0]
        $num = $numID[0] + 1;
        //导入留言：
        $query = "
            INSERT INTO messagebox (id,name,email,time,msg,shown) VALUES ('$num','$name','$email','$time','$message','2')
        ";
        $success = mysqli_query($dbc,$query) or die('error querying database'. mysqli_error($dbc));//注意，新留言默认↑显示等级（shown）为2
        mysqli_close($dbc);
        
        //返回发送信息，自动跳转：
        if ($success){
	       echo '发送成功。 <br /> ';
	       header("refresh:1;url=contact.php");
        }
		else{
			echo '抱歉，发送失败，请稍后重试。 <br /> ';
			header("refresh:3;url=contact.php");
		}
      }
    }
  else{
      $output_form = true;
    }
//显示表单主体：
  if ($output_form){

?>
<form id="contactform" name="contact" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-horizontal msgBox center-block">
    <div class="form-group">
	<label for="theName">称呼</label>
      <input id="name" name="name" type="text" class="form-control" value="<?php echo $name; ?>" />
    </div>
    <div class="form-group">
	<label for="theEmail">电子邮箱</label>
      <input id="email" name="email" type="email" class="form-control" value="<?php echo $email; ?>" />
    </div>
    <div class="form-group">
	<label for="theMsg">留言</label><br>
      <textarea id="cf_message" name="cf_message" rows="5" class="form-control"><?php echo $message; ?></textarea>
    </div>
    <button id="submit" name="submit" type="submit" class="btn btn-default">提交</button>
</form>

<?php 
  }
 ?>
	</div>
</div>
<!--留言板 展示留言-->
<div class="row latestMsg center-block">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<hr>
		<h3>最近留言</h3>
	</div>
</div>
<div class="row latestMsg center-block" id="latestMsgBest">

	<!--精选留言-->
	<?php 
      $shown_flag = 1;   //shown一栏的标注数字[是这个数字的就会被显示]2--普通留言直接显示，1--精选留言显示模式（不可叠加）
      $num_flag = 8;   //显示最新几条信息？不足此数目时就显示所有信息。

      $dbc2 = mysqli_connect(DB_HOST,DB_USER,DB_PW,DB_NAME) or die('error connecting to mysql server');
      //收集数量：
	$query_rm = "
	  SELECT count(*) FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_num_temp = mysqli_fetch_array($func_rm);//use rm_num_temp[0]
	$rm_num = $rm_num_temp[0] - 1;
	//收集时间：
	$query_rm = "
	  SELECT time FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_time = mysqli_fetch_all($func_rm);
	//收集称呼：
	$query_rm = "
	  SELECT name FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_name = mysqli_fetch_all($func_rm);
	//收集留言：
	$query_rm = "
	  SELECT msg FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_msg = mysqli_fetch_all($func_rm);
	mysqli_close($dbc2);
	//循环，显示要求的留言们，新的留言显示在前面：
     // if ($rm_num_temp[0] >= $num_flag){
	 foreach (array_reverse($rm_name) as $key => $value){    ?>
		<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 latestM">
	   <div class="latestMinner">
				<h5 class="latestMtitle">      <?php  echo implode($value);             ?>      </h5>
	  <p class="latestMtime">   (   <?php  echo implode($rm_time[$rm_num]);  ?>   )  </p>
	   <h6 class="latestMcontent"> “   <?php  echo implode($rm_msg[$rm_num]);   ?>   ”  </h6>

	 </div></div>
	  <?php 
	  $rm_num--;
	  $num_flag--;
	  if($num_flag == 0 or $rm_num == -1){break;}
	}
       ?>
</div>	
<div class="row latestMsg center-block">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<hr>
	</div>
</div>
<div class="row latestMsg center-block" id="latestMsgRecent">

		<!--最新留言-->
	<?php 
	//此部分是上面的重复。这样的留言板显示可以不断复制粘贴做很多。
      $shown_flag = 2;   //shown一栏的标注数字[是这个数字的就会被显示]2--普通留言直接显示，1--精选留言显示模式（不可叠加）
      $num_flag = 16;   //显示最新几条信息？不足此数目时就显示所有信息。

      $dbc2 = mysqli_connect(DB_HOST,DB_USER,DB_PW,DB_NAME) or die('error connecting to mysql server');
      //收集数量：
	$query_rm = "
	  SELECT count(*) FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_num_temp = mysqli_fetch_array($func_rm);//use rm_num_temp[0]
	$rm_num = $rm_num_temp[0] - 1;
	//收集时间：
	$query_rm = "
	  SELECT time FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_time = mysqli_fetch_all($func_rm);
	//收集称呼：
	$query_rm = "
	  SELECT name FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_name = mysqli_fetch_all($func_rm);
	//收集留言：
	$query_rm = "
	  SELECT msg FROM messagebox WHERE shown = '$shown_flag'
	";
	$func_rm = mysqli_query($dbc2,$query_rm) or die('error querying database');
	$rm_msg = mysqli_fetch_all($func_rm);
	mysqli_close($dbc2);
     // if ($rm_num_temp[0] >= $num_flag){
	 foreach (array_reverse($rm_name) as $key => $value){    ?>
		<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 latestM">
	 <div class="latestMinner">
				<h5 class="latestMtitle">      <?php  echo implode($value);             ?>      </h5>
	  <p class="latestMtime">   (   <?php  echo implode($rm_time[$rm_num]);  ?>   )  </p>
	   <h6 class="latestMcontent"> “   <?php  echo implode($rm_msg[$rm_num]);   ?>   ”  </h6>

	 </div>
	 </div>
	  <?php 
	  $rm_num--;
	  $num_flag--;
	  if($num_flag == 0 or $rm_num == -1){break;}
	}
       ?>
</div>
