var AJAX,
    WS = null,
    MSG,
    USER = {
        id: null,
        name: null,
        channel: null,
        key: null
    },
    STYLE = {
        active: 0,
        source: ['style','green','violet']
    },
    PAGE = 2,
    CHAT,
    tmp;

window.onload = function(){

    window.onresize = function (e){painel('chat')}



    //Iniciando LIBRARYS
    SOUND = new lib.sound();
    FILE = new lib.upload();
    FILE.set.statusElement(_('fileList'));

    //TESTE ONLY --- BEGIN
    _('help').onclick = function (e){
        if(e.target.nodeName == 'LI'){
            _('login').value = e.target.innerHTML;
            _('password').value = '1234567890';
        }
    }
    //TESTE ONLY --- END

    //Chave de segurança
    USER.key = _passw.gen(40);

    //Botão de "ENTRADA/LOGIN"
    _('go').onclick = function(){
        //USER.key = _('irsa').value;
        var login = _('login').value;
        var password = _('password').value;

        // VERIFICANDO OS DADOS INFORMADOS
        if(login.length < 5 || password.length < 5) {
            SOUND.play('error');
            return _msg(0);
        }
        if(!_isEmail(login)){
            SOUND.play('error');
            return _msg(14);
        }
        if(USER.key.length < 20) return _msg(1);

        var js = JSON.stringify({login:login, password:password, asskey:USER.key})

        //convertendo...
        var vk = RSA.encrypt(js, RSA.getPublicKey(key));

        AJAX = new lib.ajax();
        AJAX.set.url(URL+'user/checkUser');
        AJAX.set.data({key:vk});
        AJAX.set.complete(function(data){
            var e = JSON.parse(AES.dec(data, USER.key));

            if(e) {
                USER.id = e.ID;
                USER.name = e.NAME;

                //novo conteúdo...
                _('container').innerHTML =
                '<div id="xhat" class="xhat">'
                +'<div id="chat" class="painels"></div>'
                +'<textarea id="message" placeholder="your message here!"></textarea>'
                +'</div>'
                +'<div id="users" class="list painels"><h2>Usuários</h2><ul></ul></div>'
                +'<div id="groups" class="list painels"><h2>Grupos</h2><ul></ul></div>'

                +'<div id="control" class="control">'
                +'<button id="btmsg">chat</button>'
                +'<button id="btfile">file</button>'
                +'<button id="btuser">users</button>'
                +'<button id="btgroup" class="medio">groups</button>'
                +'<button id="btout">out</button>'
                +'<button id="btstyle">skin</button>'
                +'</div>';

                CHAT = _('chat');

                _('btout').onclick = function(){
                    document.location = document.location;
                };

                _('btmsg').onclick = function(){
                    painel('message');
                };

                _('btgroup').onclick = function(){
                    getUserGroupStatus();
                    painel('group');
                };

                _('btuser').onclick = function(){
                    getUserList();
                    painel('users');
                };

                _('btstyle').onclick = function(){
                    STYLE.active ++;
                    if(STYLE.active >= STYLE.source.length) STYLE.active = 0;
                    _('stylesheet_1').href = URL+'css/'+STYLE.source[STYLE.active]+'.css';

                }

                _('chat').onclick = function(){
                    painel('message');
                }

                _('message').onkeydown = function(e) {
                    if (e.which === 9) {
                        e.preventDefault();
                        e.target.value += '    ';
                    }
                };

                _('message').onkeyup = function(e) {
                    if(e.which == 13 && e.target.value.trim().length > 0) {
                        if (e.target.value.trim() === '' || e.shiftKey) return false;
                        else sendMsg(e.target.value);
                    }
                };

                //carregando  listagem de usuários
                setTimeout(function(){
                    getUserList();
                    painel('chat');
                }, 200);

                //carregando listagem de grupos
                _qs('#groups UL').innerHTML = mountList(e.li);
                painel('group');

                //Start Web Socket
                startWs();

            } else {
                _msg(2, 4000);
                SOUND.play('error');
            }
        })
        AJAX.send();
    }
}



var MSG = {

    show: function (msg){
        var d = document.createElement('DIV');
        d.className = 'msg'+(msg.userid == USER.id ? ' me':'');
        var m = '<img src="'+URL+'img/d'+parseInt(msg.userid.toString().substr(-1))+'.jpg">'
                +'<h2>'+(msg.userid == USER.id ? '&lt;You&gt;':msg.name)+'</h2>'
                +'<span class="msgdate">'+("undefined" !== typeof msg.date ? msg.date : this.dtime())+' '+' + AES 256</span>'
                +'<div class="msgtxt">'+msg.message.replace(/(  )/g, " &nbsp;")+'</div>'
                +'</div>';
        d.innerHTML = '<p class="inline"><img src="img/icon/ldi.gif">decodificando...</p>';

        CHAT.insertBefore(d, null);

        setTimeout(function(){ d.innerHTML = m; scroll();}, 500);
        scroll();
        return d;
    },

    dtime: function(){
        var t = new Date();
        var d = t.getDay();
        var m = t.getMonth();
        var y = t.getFullYear();

        var h = t.getHours();
        var i = t.getMinutes();
        var s = t.getSeconds();

        d = d < 10 ? '0'+d : d;
        m = m < 10 ? '0'+m : m;
        h = h < 10 ? '0'+h : h;
        i = i < 10 ? '0'+i : i;
        s = s < 10 ? '0'+s : s;

        return y+'-'+m+'-'+d+' '+h+':'+i+':'+s;
    }
}


function mountList(o, g){
    var h = ("undefined" == typeof g || g === false) ? false : true;
    var a = '';
    for(var i in o){
        var n = o[i]['msg'];
        var t = o[i]['total'];
        var n = n > 0 ? ' - ' +(n > 1 ? n+' novas' : n+' nova') : '';
        //var t = t > 0 ? (t > 1 ? 't+' mensagens' : t+' mensagem'):'';
        var t = t > 0 ? t+ ' mensage' + (t > 1 ? 'ns' : 'm') : '';

        a += '<li id="lst'+i+'" onclick="listMsgGroup('+i+')" '+(h === false ? 'class="selected"' : '')+'>'
             +o[i]['name']+'<span class="desc">'
             +o[i]['title']+'</span><span class="count">'
             +t+n+'</span></li>';

        if(h === false){ //get messages from first group!
            h = true;
            getMsgByGroup(i);
        }

        if("undefined" != typeof o[i]['content']) a += mountList(o[i]['content'], h);
    }
    return a;
}

function mountMsg(o){
    CHAT.innerHTML = '';

    for(var i in o){
        if("undefined" === typeof o[i]['type']) continue;
        var msg = {
            type: o[i]['type'],
            message: o[i]['content'],
            channel: o[i]['group'],
            name: o[i]['name'],
            userid: o[i]['id'],
            date: o[i]['date']
        }
        MSG.show(msg);
    }
}

function listMsgGroup(g){
    //marcando a seleção do menu
    listSelect('lst'+g);

    painel('chat');
    return getMsgByGroup(g);
}

function getMsgByGroup(g){
    //Pegando os dados no servidor
    AJAX.set.url(URL+'msg/getMsgByGroup/');
    AJAX.set.data({enc:AES.enc(JSON.stringify({group:g}), USER.key), id:USER.id});
    AJAX.set.complete(function(data){
        var e = JSON.parse(AES.dec(data, USER.key));

        if(e) mountMsg(e);
        else  {
            CHAT.innerHTML = '';
            _msg(3);
        }
        USER.channel = g;
    })
    AJAX.send();
}

function getUserGroupStatus(){
    AJAX.set.url(URL+'msg/getUserGroupStatus');
    AJAX.set.data({enc:AES.enc(JSON.stringify({group:'nada'}), USER.key), id:USER.id});

    AJAX.set.complete(function(data){
        var e = JSON.parse(AES.dec(data, USER.key));
        _qs('#groups UL').innerHTML = '';

        if(e) _qs('#groups UL').innerHTML = mountList(e.li);
        else {
            _msg(4, 2000);
            SOUND.play('error');
            exit();
        }
    })
    AJAX.send();
}

/* Remove all class "selected"
 *      and select element 'id'
 */
function listSelect(id){
    //marcando a seleção do menu
    var l = _qa('.selected');
    for(var i = 0; i < l.length; i++){
        l[i].classList.remove('selected');
    }
    _(id).classList.add('selected');
}

function getMsgUser(u){
    listSelect('user'+u);
    alert(LANG[5]+u+' ?!');
}

function getUserList(){
    AJAX.set.url(URL+'user/getUserList');
    AJAX.set.data({enc:AES.enc(JSON.stringify({group:'nada'}), USER.key), id:USER.id});

    AJAX.set.complete(function(data){
        var e = JSON.parse(AES.dec(data, USER.key));

        if(e) mountUserList(e);
        else {
            _msg(4, 2000);
            SOUND.play('error');
            exit();
        }
    })
    AJAX.send();
}

function mountUserList(u){
    var d = '';
    for(var i in u){
        if(u[i]['type'] !== 'msg') continue;
        var t = u[i]['total'];
        var t = t > 0 ? t+ ' mensage' + (t > 1 ? 'ns' : 'm') : LANG[3];
        d += '<li id="user'+u[i].id+'" onclick="getMsgUser('+i+')">'+u[i].name
             //+'<span class="desc">+Recente: '+u[i]['last']+'</span>'
             +'<span class="count">'+t+'.</span></li>';
    }
    _qs('#users UL').innerHTML = d;
}

function painel(p){
    if(p == 'message') {
        var m = _('message');
        if(m.style.display == 'block') m.style.display = 'none';
        else m.style.display = 'block';
        return _('message').focus();
    }

    if("undefined" === typeof _('xhat')) return;
    var w = _('xhat').clientWidth;

    if(w >= 750) {
        _('groups').style.display = 'block';
        _('users').style.display = 'block';
        _('chat').style.display = 'block';
    }

    if(w >= 550 && w < 750){
        _('chat').style.display = 'block';
        _('groups').style.display = 'block';
        _('users').style.display = 'none';

        if(p == 'group') {
            _('groups').style.display = 'block';
            _('users').style.display = 'none';
        }
        if(p == 'users') {
            _('users').style.display = 'block';
            _('groups').style.display = 'none';
        }
    }

    if(w < 550){
        _('groups').style.display = 'none';
        _('chat').style.display = 'none';
        _('users').style.display = 'none';

        if(p == 'group') _('groups').style.display = 'block';
        if(p == 'chat') _('chat').style.display = 'block';
        if(p == 'users') _('users').style.display = 'block';
    }
}

function scroll(){
    setTimeout(function(){_('chat').scrollTop = _('chat').scrollHeight}, 100);
}