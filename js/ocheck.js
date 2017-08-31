//Created by lx on 2016/11/15. 部分修改 hx 2017/6/29
    var chxm = false;
    var chQQ = false;
    var chphone = false;
    var chMail = false;
    var isTrue = 0;
    var chCard = false;
    var chMoney = false;
    var chNofans = false;
    var chPattern = false;
    var chName = false;
    var chKong = false;
    var chNonumber = false;
//var oCHCK = function () {
    
    var oCheck = document.getElementById('ocheck');    //父级IDIdName  oChxm
    function getByClass(o, s)//获取Class;
    {
        var aEle = document.getElementsByTagName('*');
        var arr = [];
        for (var i = 0; i < aEle.length; i++) {
            if (aEle[i].className == s) {
                arr.push(aEle[i])
            }
        }
        return arr;
    }

//姓名校验
    //ClassName  oChxm
    function oChxm() {
        var oChxm = $(".oChxm")[0];
        if(oChxm==undefined){chxm = true; return;};
        var reQQ = /[\u4E00-\u9FA5]/g;
        oChxm.onkeyup = function () {
            if (this.value.length > 6) {
                this.value = this.value.substr(0, 6)
            }
            if (reQQ.test(this.value)) {
                this.nextSibling.innerHTML = '输入正确';
                this.nextSibling.className = '';
                this.nextSibling.className = 'ingreen';
                chxm = true;
                return;
            } else {
                this.nextSibling.innerHTML = '请输入正确的名字';
                this.nextSibling.className = '';
                this.nextSibling.className = 'inred';
                chxm = false;
                return;
            }
        }

    }

    oChxm();
// 手机校验
    //ClassName  oChphone
    function oChphone() {
        var phone = $(".oChphone")[0];
         if(phone==undefined){return chphone = true;
};
        var reQQ = /^[1]\d{10}$/;
        function phoneChack(){
            if (phone.value.length > 11) {
                phone.value = phone.value.substr(0, 11)
            }
            if (reQQ.test(phone.value)) {
                phone.nextSibling.innerHTML = '输入正确';
                phone.nextSibling.className = '';
                phone.nextSibling.className = 'ingreen';
                chphone = true;
                return;
            } else {
                phone.nextSibling.innerHTML = '请输入正确的手机号码';
                phone.nextSibling.className = '';
                phone.nextSibling.className = 'inred';
                chphone = false;
                return;
            }
        }
        phone.onkeyup = phoneChack;
        phoneChack();
    }

// QQ校验
    //ClassName  oChQQ
    function oChQQ() {
        var oChQQ = $(".oChQQ")[0];
         if(oChQQ==undefined){return chQQ = true;};
        var reQQ = /^[1-9]\d{5,12}$/;
        oChQQ.onkeyup = function () {
            if (this.value.length >= 14) {
                this.value = this.value.substr(0, 14)
            }
            if (reQQ.test(this.value)) {
                this.nextSibling.innerHTML = '输入正确';
                this.nextSibling.className = '';
                this.nextSibling.className = 'ingreen';
                chQQ = true;
                return;
            } else {
                this.nextSibling.innerHTML = '请输入正确的QQ号码';
                this.nextSibling.className = '';
                this.nextSibling.className = 'inred';
                chQQ = false;
                return;
            }
        }
    }

    oChQQ();
//邮箱校验
    function oChmail() {
        var oChmail = $(".oChmail")[0];
        if(oChmail==undefined){return chMail = true;};
        var reMail = /^\w+@[a-z0-9]+\.[a-z]+$/i;
        function emailChack(){
            if (oChmail.value.length >= 30) {
                oChmail.value = oChmail.value.substr(0, 30)
            }
            if (reMail.test(oChmail.value)) {
                oChmail.nextSibling.innerHTML = '输入正确';
                oChmail.nextSibling.className = '';
                oChmail.nextSibling.className = 'ingreen';
                chMail = true;
                return;
            } else {
                oChmail.nextSibling.innerHTML = '请输入正确的邮箱';
                oChmail.nextSibling.className = '';
                oChmail.nextSibling.className = 'inred';
                chMail = false;
                return;
            }
        }
        oChmail.onkeyup = emailChack;
        emailChack();
    }

//身份证校验
    function oChcard() {
        var oChcard = $(".oChcard")[0];
        if(oChcard==undefined){return chCard = true;};
        var reCard = /\d{17}[\d|x]|\d{15}/;
        function cardChack(){
            if (reCard.test(oChcard.value)) {
                oChcard.nextSibling.innerHTML = '输入正确';
                oChcard.nextSibling.className = '';
                oChcard.nextSibling.className = 'ingreen';
                chCard = true;
                return;
            } else {
                oChcard.nextSibling.innerHTML = '请输入正确的身份证';
                oChcard.nextSibling.className = '';
                oChcard.nextSibling.className = 'inred';
                chCard = false;
                return;
            }
        }
        oChcard.onkeyup = cardChack;
        cardChack();
    }
    
    
//余额校验(非负数)
    function oChmoney() {
        var oChmoney = $(".oChmoney")[0];
         if(oChmoney==undefined){return chMoney = true;};
        var reMoney = /[0-9]\d*/;
        function moneyChack(){
            if (reMoney.test(oChmoney.value)&&oChmoney.value>=0) {
                oChmoney.nextSibling.innerHTML = '输入正确';
                oChmoney.nextSibling.className = '';
                oChmoney.nextSibling.className = 'ingreen';
                chMoney = true;
                return;
            } else {
                oChmoney.nextSibling.innerHTML = '请输入一个非负数';
                oChmoney.nextSibling.className = '';
                oChmoney.nextSibling.className = 'inred';
                chMoney = false;
                return;
            }
        }
        oChmoney.onkeyup = moneyChack;
        moneyChack();
    }
    
//匹配积分(非负整数)
    function oChnofans() {
        var oChnofans = $(".oChnofans")[0];
         if(oChnofans==undefined){return chNofans = true;};
        var reNofans = /[0-9]\d*/;
        console.log($(".oChnofans").val());
        function fansChack(){
            if (reNofans.test(oChnofans.value)&&oChnofans.value>=0) {
                oChnofans.nextSibling.innerHTML = '输入正确';
                oChnofans.nextSibling.className = '';
                oChnofans.nextSibling.className = 'ingreen';
                chNofans = true;
                return;
            } else {
                oChnofans.nextSibling.innerHTML = '请输入一个非负数';
                oChnofans.nextSibling.className = '';
                oChnofans.nextSibling.className = 'inred';
                chNofans = false;
                return;
            }
        }
        oChnofans.onkeyup = fansChack;
        fansChack();
    }
    

//匹配正整数
    function oChpattern () {
        var oChpattern = $(".oChpattern")[0];
         if(oChpattern==undefined){return chPattern = true;};
        var rePattern = /^[0-9]*[1-9][0-9]*$/;
        function patternChack(){
            if (rePattern.test(oChpattern.value)&&oChpattern.value>0) {
                oChpattern.nextSibling.innerHTML = '输入正确';
                oChpattern.nextSibling.className = '';
                oChpattern.nextSibling.className = 'ingreen';
                chPattern = true;
                return;
            } else {
                oChpattern.nextSibling.innerHTML = '请输入一个正整数';
                oChpattern.nextSibling.className = '';
                oChpattern.nextSibling.className = 'inred';
                chPattern = false;
                return;
            }
        }
        oChpattern.onkeyup =patternChack;
        patternChack();
    }
//匹配正整数和0
    function oChNonumber () {
        var oChNonumber = $(".oChNonumber")[0];
         if(oChNonumber==undefined){return chNonumber = true;};
        var reNonumber = /^[0-9]*[1-9][0-9]*$/;
        function NonumberChack(){
            if (reNonumber.test(oChNonumber.value)||oChNonumber.value=='0') {
                oChNonumber.nextSibling.innerHTML = '输入正确';
                oChNonumber.nextSibling.className = '';
                oChNonumber.nextSibling.className = 'ingreen';
                chNonumber = true;
                return;
            } else {
                oChNonumber.nextSibling.innerHTML = '请输入一个非负整数';
                oChNonumber.nextSibling.className = '';
                oChNonumber.nextSibling.className = 'inred';
                chNonumber = false;
                return;
            }
        }
        oChNonumber.onkeyup =NonumberChack;
        NonumberChack();
    }
    
//匹配用户名
    function oChname () {
        var oChname = $(".oChname")[0];
         if(oChname==undefined){return chName = true;};
        var reName = /[A-Za-z0-9_\-\u4e00-\u9fa5]+/;
        function nameChack(){
            if (reName.test(oChname.value)) {
                oChname.nextSibling.innerHTML = '输入正确';
                oChname.nextSibling.className = '';
                oChname.nextSibling.className = 'ingreen';
                chName = true;
                return;
            } else {
                oChname.nextSibling.innerHTML = '请输入用户名';
                oChname.nextSibling.className = '';
                oChname.nextSibling.className = 'inred';
                chName = false;
                return;
            }
        }
        oChname.onkeyup = nameChack;
        nameChack();
    }
    
//匹配非空
    function oChkong () {
        var oChkong = $(".oChkong")[0];
         if(oChkong==undefined){return chKong = true;};
        var reKong = /[A-Za-z0-9_\-\u4e00-\u9fa5]+/;
        function kongChack(){
            console.log(oChkong.value);
            if (reKong.test(oChkong.value)) {
                oChkong.nextSibling.innerHTML = '输入正确';
                oChkong.nextSibling.className = '';
                oChkong.nextSibling.className = 'ingreen';
                chKong = true;
                return;
            } else {
                oChkong.nextSibling.innerHTML = '请输入相关信息';
                oChkong.nextSibling.className = '';
                oChkong.nextSibling.className = 'inred';
                chKong = false;
                return;
            }
        }
        oChkong.onkeyup = kongChack;
        oChkong.onblur = function(){
            setTimeout(kongChack,500);
        }
        kongChack();
    }
    


    
//};
//oCHCK();
//发送验证
function oCHCK(){
    oChphone();oChcard();oChmoney();oChnofans();oChpattern();oChname();oChkong();oChmail();oChNonumber();
}
function oCheckSbumit1() {
        var chckevalue = false;
        if (chxm == true) {
            chckevalue = true;
        } else {
//            alert('请输入名字');
            $('body').dialog({type:'danger',discription:'请输入名字'});
            return false;
        }
        if (chphone == true) {
            chckevalue = true;
        } else {
//            alert('请输入手机号码');
            $('body').dialog({type:'danger',discription:'请输入手机号码'});
            return false;
        }
        if (chQQ == true) {
            chckevalue = true;
        } else {
//            alert('请输入QQ号码');
            $('body').dialog({type:'danger',discription:'请输入QQ号码'});
            return false;
        }
        if (chMail == true) {
            chckevalue = true;
        } else {
//            alert('请输入邮箱');
            $('body').dialog({type:'danger',discription:'请输入邮箱'});
            return false;
        }
        if (chCard == true) {
            chckevalue = true;
        } else {
//            alert('请输入身份证');
            $('body').dialog({type:'danger',discription:'请输入身份证'});
            return false;
        }
        if (chMoney == true) {
            chckevalue = true;
        } else {
//            alert('请输入非负数');
            $('body').dialog({type:'danger',discription:'请输入非负数'});
            return false;
        }
        if (chNofans == true) {
            chckevalue = true;
        } else {
//            alert('请输入非负整数');
            $('body').dialog({type:'danger',discription:'请输入非负数'});
            return false;
        }
        if (chPattern == true) {
            chckevalue = true;
        } else {
//            alert('请输入一个正整数');
            $('body').dialog({type:'danger',discription:'请输入一个正整数'});
            return false;
        }
        if (chName == true) {
            chckevalue = true;
        } else {
//            alert('请输入用户名');
            $('body').dialog({type:'danger',discription:'请输入用户名'});
            return false;
        }
        if (chKong == true) {
            chckevalue = true;
        } else {
//            alert('请输入用户名');
            $('body').dialog({type:'danger',discription:'请输入相关信息'});
            return false;
        }
        if (chNonumber == true) {
            chckevalue = true;
        } else {
//            alert('请输入用户名');
            $('body').dialog({type:'danger',discription:'请输入一个非负整数'});
            return false;
        }
        if (chckevalue == true) {
            isTrue = 1;
            return isTrue;
        }
    }