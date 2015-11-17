<?php
require_once('function/basic.php');
require_once('function/config.php');
echoheader();
?>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<div class="container theme-showcase">
    <div class="page-header">
        <h1>Password Manager</h1>
	</div>
	<p>RECOVERY:<br /></p>
    <p>The recovery process will be on your browser. It's safe!</p>
	<form>
      <p style="color:red">Paste all contents in backup.txt into the following box</p>
    <textarea id="backupc"></textarea>
    <p> </p>
    <p>Password: <input type="password" name="pwd" id="pwd" /></p><br />
    <p style="color:red">Input the login password when you generate the backup file.</p>
    </form>
    <input type="button" class="btn btn-md btn-success" onClick="rec();" id="chk" value="RECOVER IT!" />
    <a href="../" class="btn btn-md btn-info">Go Back</a>
    <p> </p>
    <p><br /> </p>
    <div id="recover_result" style="display:none">
    <p>The following table shows your accounts and passwords if you enter the correct login password. If the data loss is caused by attack, please update your passwords anyway! You can copy your passwords and paste them in your new password manager account.</p>
    <table class="table" id="rtable"></table>
    </div>
<script type="text/javascript">
var JSsalt='';
var PWsalt='';
var ALPHABET='';
var secretkey='';
var confkey='';
function sanitize_json(s){
    var t=s;
    t=t.replace(/\n/g,'')
    return t.replace(/\r/g,'');
}
function gen_key()
{
    var pass=$("#pwd").val();
    secretkey=String(pbkdf2_enc(pass,JSsalt,500));
    secretkey=String(CryptoJS.SHA512(secretkey+PWsalt));
    confkey=pbkdf2_enc(String(CryptoJS.SHA512(pass)),JSsalt,100);
}
function gen_account_array(enc_account_array)
{
    var tempchar,x;
    var account_array=new Array();
    for (x in enc_account_array){
        tempchar=decryptchar(enc_account_array[x],secretkey);
        if (tempchar=="") tempchar="Oops, there's some errors!"
        account_array[x]=tempchar;
    }
    return account_array;
}
function gen_pass_array(account_array,enc_pass_array)
{
    var tempchar,x,name;
    var pass_array=new Array();
    for (x in enc_pass_array){
        tempchar=decryptchar(enc_pass_array[x],secretkey);
        if (tempchar=="") {
            tempchar="Oops, there's some errors!";
        }else{
            name=account_array[x];
            tempchar=get_orig_pwd(confkey,PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,tempchar);
        }
        pass_array[x]=tempchar;
    }
    return pass_array;
}
function rec(){
    var json=JSON.parse(sanitize_json($("#backupc").val()));
    if(json.status!="OK") {
        alert("INVALID BACKUP FILE");
        return;
    }
    JSsalt = json.JSsalt;
    PWsalt = json.PWsalt;
    ALPHABET = json.ALPHABET;
    if($("#pwd").val()==''){
        alert("EMPTY PASSWORD IS NOT ALLOWED");
        return;
    }
    $("#recover_result").hide();
    $("#chk").attr("disabled",true);
    $("#chk").attr("value", "Processing...");
    gen_key();
    var enc_pass=new Array();
    var enc_acc=new Array();
    var x;
    for(x in json.data){
        enc_acc[x]=json.data[x][0];
        enc_pass[x]=json.data[x][1];
    }
    var acc_array=gen_account_array(enc_acc);
    var pass_array=gen_pass_array(acc_array,enc_pass);
    var html='<tr><th>Account</th><th>Password</th></tr>';
    for(x in acc_array){
        html=html+'<tr><td>'+acc_array[x]+'</td><td>'+pass_array[x]+'</td></tr>';
    }
    $("#rtable").html(html);
    $("#recover_result").show();
    $("#chk").removeAttr("disabled");
    $("#chk").attr("value", "RECOVER IT!");
}
</script>
</div>
<?php echofooter();?>
