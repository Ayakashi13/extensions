
/**
 * Allows users to quote posts without a page reloading
 *
 * @copyright Copyright (C) 2008 PunBB
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package pun_quote
 */

document.onmouseup = SetSelected;

function turnOnLinks()
{
	var p = document.getElementsByTagName('p');

	var tmp_k = 0;

	for (var k=0; k < p.length; k++ )
	{
		if (p[k].className.match(/post-actions/))
		{
			var span = p[k].getElementsByTagName('span');
	
			if (tmp_k == 0)
			{
				span[5].style.display = "";
				tmp_k = 1;
				var a = span[7].getElementsByTagName('a');

				if (a.length == 0)
				{
					tmp_k = 1;
					k = 0;
				}
				else
					a[0].href = "javascript: QuickQuote()";
			}
			else
			{
				span[6].style.display = "";
				var a = span[8].getElementsByTagName('a');
				a[0].href = "javascript: QuickQuote()";
			}
		}
	}
}

function getSelectedText()
{
	var result = '';
	if (document.selection)
		result = document.selection.createRange().text;
	else if (document.getSelection)
		result = document.getSelection();
	else if (window.getSelection)
		result = window.getSelection();
	else
		return result;
	return result;
}

function TrimString(param)
{
	param = param.replace(/ /g,' ');
	return param.replace(/(^\s+)|(\s+$)/g, '');
}

function Reply(tid_param, qid_param)
{
	var element = document.getElementsByTagName('div');
	for (var i=0; i < element.length; i++)
	{
		if(element[i].className.match(/^post\s.*/ig))
		{
			var post = new String(element[i].innerHTML);

			if(post.search('Reply[(]' + tid_param + ',' + qid_param + '[)]') != -1)
			{
				post=ChangePost(post);
				var post_new = RemoveSymbols(post);
				var selected_text = (window.selected_text_first == '')?window.selected_text_second:window.selected_text_first;//getSelectedText();
				var reply_url = document.getElementById("pun_quote_url");
				reply_url = reply_url.value;
				var replace_url;

				if((selected_text != undefined)&&(selected_text!=''))
				{
					//this is for Chrome browser. Text, selected by user, has 'Range' type, not 'String'. And in some cases, when there is no text selected, Chrome returns one symbol of 'Caret' type
					if((selected_text.type=='Range')||(selected_text.type=='Caret'))
					selected_text=selected_text.toString();
					
					selected_text = RemoveSymbols(selected_text);
					
					post = TrimString(post);
					
					if((post_new.indexOf(selected_text) != -1) && (selected_text.charAt(0) != ''))
					{
						var form = document.getElementById('qq');
						//form.action='post.php?tid=' + tid_param + '&qid=' + qid_param;
						replace_url = reply_url.replace('$2',qid_param.toString());
						form.action = replace_url.toString();
						element = document.getElementById('post_msg');
						element.value =(window.selected_text_first == '')?window.selected_text_second:window.selected_text_first;//getSelectedText();
						form.submit();
						break;
					}
				}
				replace_url = reply_url.replace('$2',qid_param.toString());
				location = replace_url.toString();
			}
		}
	}
	
	return false;
}

function QuickQuote(tid_param, qid_param)
{
	var element = document.getElementsByTagName('div');

	for (var i=0; i < element.length; i++)
	{

		if (element[i].className.match(/^post\s.*/ig))
		{
		
			var post = new String(element[i].innerHTML);
			if (post.search('QuickQuote[(]' + tid_param + ',' + qid_param + '[)]') != -1)
			{
				//get quoted author name from the post
				//var RegExp = /<cite>.*\sby\s(.*?):/ig;  old markup compatibility
				var RegExp =/<span class="*post-byline"*>(?:.*?)<a(?:.*?)>(.*?)<\/a>/ig;
				var result =  RegExp.exec(post);
				RegExp.lastIndex=0;
				var author_name;
				
				if(result!=null)
					author_name=result[1];


				post=ChangePost(post);
				var post_new = RemoveSymbols(post);
				var selected_text = (window.selected_text_first == '')?window.selected_text_second:window.selected_text_first;//getSelectedText();


				post = TrimString(post);

				if ((selected_text != undefined)&&(selected_text!=''))
				{
					//this is for Chrome browser. Text, selected by user, has 'Range' type, not 'String'. And in some cases, when there is no text selected, Chrome returns one symbol of 'Caret' type
					if((selected_text.type=='Range') || (selected_text.type=='Caret'))
						selected_text=selected_text.toString();

					selected_text = RemoveSymbols(selected_text);

					if ((post_new.indexOf(selected_text) != -1) && (selected_text.charAt(0) != ''))
					{
						element = document.getElementById('fld1');
						element.value +=(window.selected_text_first == '')?'[quote='+author_name+']'+window.selected_text_second+'[/quote]'+'\n':'[quote='+author_name+']'+window.selected_text_first+'[/quote]'+'\n';//getSelectedText();
						break;
					}
				}
				element = document.getElementById('fld1');
				element.value+='[quote='+author_name+']'+post+'[/quote]'+'\n';
			}
		}
	}

	return false;
}

function RemoveSymbols(string)
{
	string = string.replace(/\r*/gi,'');
	string = string.replace(/\n*/gi,'');
	string = string.replace(/\s*/gi,'');
	string = string.replace(/\u00A0/g,' ');
	string = string.replace(/&nbsp;/g,' ');
	string = string.replace(/&lt;/g,'<');
	string = string.replace(/&gt;/g,'>');
	string = string.replace(/<BR>/ig,'');
	return string;
}
function ChangePost(post)
{
	var reg = new RegExp('<DIV[\\s]*class[\\s]*=[\\s]*["]*[\\s]*entry\\-content[\\s]*["]*[\\s]*>[\\s\\S]*<DIV[\\s]*class[\\s]*=[\\s]*["]*[\\s]*postfoot[\\s]*["]*[\\s]*>','ig');

	var post = new String(reg.exec(post));

	var browse = navigator.userAgent.toLowerCase();

	post = post.replace(/((<BR>)(<\/P>))|((<BR\/>)(<\/P>))/ig,'$2$4');

	if(browse.indexOf('opera') == -1)
		post = post.replace(/((<BR>)(<P>))|((<BR\/>)(<P>))/ig,'$2$4');

	post = post.replace(/(:?<BR>)|(:?<BR\/>)/ig,'\n');

	//</p><p> = \n\n  - Opera FF
	//</p><p> = /n - IE 7.0
	if(browse.indexOf('opera') != -1 ||  browse.indexOf('gecko') != -1)
		post = post.replace(/(:?<\/p>)|(:?<p>)/ig,'\n');
	else
		post = post.replace(/<\/p>[\s]*<p>/ig,'\n');

	post = post.replace(/>[\s]*</,'><');

	//Make [quote="name"]...[/quote]
	post = post.replace(/<div[\s]*class[\s]*=[\s]*["]*[\s]*quotebox[\s]*["]*[\s]*>[\s]*<cite>/ig,'[quote=');
	post = post.replace(/<div[\s]*class[\s]*=[\s]*["]*[\s]*quotebox[\s]*["]*[\s]*>/ig,'[quote]');
	post = post.replace(/[\s]*wrote:/g,"]");
	post = post.replace(/[\s]*<\/blockquote>[\s]*/ig,'[/quote]\n');

	//Handle BB-codes
	//code
	post = post.replace(/<div[\s]*class[\s]*=[\s]*["]*[\s]*codebox[\s]*["]*[\s]*>[\s]*<pre>[\s]*<code>/ig,'[code]');
	post = post.replace(/[\s]*<\/code>[\s]*<\/pre>[\s]*<\/div>/ig,'[/code]\n');	

	//b
	post = post.replace(/[\s]*<strong>[\s]*/ig,'[b]');
	post = post.replace(/[\s]*<\/strong>[\s]*/ig,'[/b]');

	//list
	post = post.replace(/[\s]*<ul>[\s]*/ig,'[list]');
	post = post.replace(/[\s]*<ol[\s]*class[\s]*=[\s]*["]decimal["][\s]*>[\s]*/ig,'[list=1]');
	post = post.replace(/[\s]*<ol[\s]*class[\s]*=[\s]*["]alpha["][\s]*>[\s]*/ig,'[list=a]');
	post = post.replace(/[\s]*<li>[\s]*/ig,'[*]');
	post = post.replace(/[\s]*<\/li>[\s]*/ig,'[/*]');
	post = post.replace(/[\s]*<\/ol>[\s]*/ig,'[/list]');
	post = post.replace(/[\s]*<\/ul>[\s]*/ig,'[/list]');

	for (temp = 0; temp < 6; temp++)
	{
		//color
		post = post.replace(/<span[\s]*style[\s]*=[\s]*["]*color[\s]*:[\s]*([\#a-zA-Z0-9]*)\;{1}["]*[\s]*>([^\<]*)<\/span>[\s]*/ig,'[color=$1]$2[/color]');
		//u
		post = post.replace(/[\s]*<span[\s]*class[\s]*=[\s]*["]*bbu["]*[\s]*>([^\<]*)<\/span>[\s]*/ig,'[u]$1[/u]');	
		//i
		post = post.replace(/[\s]*<em>([^\<]*)<\/em>[\s]*/ig,'[i]$1[/i]');
		//h
		post = post.replace(/[\s]*<h5>([^\<]*)<\/h5>[\s]*/ig,'[i]$1[/i]');
		//email
		post = post.replace(/<a[\s]*href=["]*mailto:[\s]*([^]*)["]*[\s]*>([^]*)<\/a>/ig,'[email=\"$1\"]$2[/email]');
		//url
		post = post.replace(/<a[\s]*href=["]*http\:\/\/([^]*)["]*[\s]*>([^]*)<\/a>/ig,'[url=\"$1\"]$2[/url]');
	}
	
	//Remove signature block
	post = post.replace(/<div[\s]*class[\s]*=[\s]*["]*[\s]*sig-content[\s]*["]*[\s]*>(.*)<\/div>/gi,'');

	//Remove tags
	post = post.replace(/<(:?.*?)>/gi,'');

	//Replace quote = name name on quote = "name name"
	post = post.replace(/\[quote=(["][-a-zA-Z0-9]*)[\s]+([-"a-zA-Z0-9]*)\]/g,'[quote=\"$1 $2\"]');

	//Insert \n before [/quote]
	post = post.replace(/\]\[\/quote\]/g,']\n[/quote]');

	//exotic symbols =)
	post = post.replace(/\u00A0/g,' ');
	post = post.replace(/&nbsp;/g,' ');
	post = post.replace(/&lt;/g,'<');
	post = post.replace(/&gt;/g,'>');
	return post;
}

function SetSelected()
{
	switch(window.selected_text_pointer)
	{
		case 0:
			window.selected_text_pointer = 1;
			window.selected_text_first = getSelectedText();
		break;
		case 1:
			window.selected_text_pointer = 0;
			window.selected_text_second = getSelectedText();
		break;
		case undefined:
			window.selected_text_pointer = 0;
			window.selected_text_second = getSelectedText();
		break;
	}
}
