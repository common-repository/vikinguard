function test() {
	console.log("TEST");

}

function sendMail(advise, error) {

	var email = document.getElementById("checkEmail").value;
	var send = true;
	var text = "";

	if (!validateEmail(email)) {
		text = text + advise;
		send = false;
	}

	if (send) {

		checkcallbackajax(
				"https://vikinguard.com/heimdalapp/api/external/customer/exists/"
						+ email, error).done(function(data, statusCode) {
		});
	} else {
		alert(text);
	}
}

function validateEmail(email) {
	var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return re.test(email);
}

function checkcallbackajax(uri, data, error) {

	var request = {
		url : uri,
		contentType : "application/json",
		accepts : "application/json",
		cache : false,
		dataType : 'jsonp',
		data : 'jsonp',
		success : function(json) {
			if (json.exists) {
				var email = document.getElementById("checkEmail").value;

				window.location.replace(window.location.href
						+ "&action=configuration&heimdalapm_email=" + email);
			} else {
				var email = document.getElementById("checkEmail").value;

				window.location.replace(window.location.href
						+ "&action=signup&heimdalapm_email=" + email);
			}
		},
		error : function(e) {
			alert(error);

		}
	};

	return jQuery.ajax(request);
};

function signupMail(check, error) {
	var email = document.getElementById("signinEmail").value;
	var password = document.getElementById("signinPassword").value;
	remembercallbackajax(
			"https://vikinguard.com/heimdalapp/api/external/user/credentials/?email="
					+ encodeURIComponent(email) + "&password=" + encodeURIComponent(password) + "", check, error)
			.done();
}

function remembercallbackajax(uri, check, error) {
	var request = {
		url : uri,
		contentType : "application/json",
		accepts : "application/json",
		cache : false,
		dataType : 'jsonp',
		data : 'jsonp',
		success : function(json) {
			if ( json.status != 200) {
				alert(check);
			} else {
				var email = document.getElementById("signinEmail").value;
				var password = document.getElementById("signinPassword").value;

				window.location.replace(window.location.href
						+ "&action=multishop&heimdalapm_email=" + encodeURIComponent(email)
						+ "&heimdalapm_password=" + encodeURIComponent(password)
						+ "&heimdalapm_customer=" + encodeURIComponent(json.customerId)
						+"&heimdalapm_customer_info=" + encodeURIComponent(JSON.stringify(json)));

			}
		},
		error : function(e) {
			alert(error);
		}
	};

	return jQuery.ajax(request);
};

function shopSelected(customerId,adviseTerms){
	var shopId = document.getElementById("multishop_selector").value;
	
	if (!document.getElementById("signupTerms").checked) {
		alert(adviseTerms);
	}else{
		window.location.replace(window.location.href
			+ "&action=configured" 
			+ "&heimdalapm_customer=" + encodeURIComponent(customerId)
			+ "&heimdalapm_shop=" + encodeURIComponent(shopId));
	}
}

function reconfigured() {
	window.location.replace(window.location.href + "&action=reconfigured");
}

var said = false;

function validateEmail(email) {
	var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	return re.test(email);
}

function signup(heimdalapm_email, adviseShort, adviseStart, advisePassword,
		adviseConfirm, adviseEmail, adviseTerms, adviseLocalhost, error, heimdalEmail) {

	var customerName = document.getElementById("signupCustomer").value;
	var email = heimdalapm_email;
	var password = document.getElementById("signupPassword").value;
	var shopURL = document.getElementById("signupShop").value;
	var confirm = document.getElementById("signupConfirm").value;
	
	var send = true;
	var text = "";

	if (customerName.length < 6) {
		text = adviseShort;
		send = false;

	}

	if (!strStartsWith(shopURL, 'http')) {
		text = text + adviseStart;
		send = false;

	}

	if (confirm.length < 6) {
		text = text + advisePassword;
		send = false;

	}

	if (confirm != password) {
		text = text + adviseConfirm;
		send = false;

	}
	
//	if(!password.match(/^([a-zA-Z0-9]{6,})$/)){
//		text=text+" only passwords with letters and numbers are allowed\n";
//			send=false;
//
//	}

	if (!validateEmail(email)) {
		text = text + adviseEmail;
		send = false;

	}
	if (!document.getElementById("signupTerms").checked) {
		text = text + adviseTerms;
		send = false;

	}
	if ((shopURL.indexOf("localhost") > -1)
			|| (shopURL.indexOf("127.0.0.1") > -1)) {

		if (!said) {
			text = adviseLocalhost;
			send = false;
		}
		said = true;

	}

	if (send) {
	
		signupcallbackajax(
				"https://vikinguard.com/heimdalapp/api/external/customer?customerName="
						+ encodeURIComponent(customerName) + "&mail=" + encodeURIComponent(email) + "&password="
						+ encodeURIComponent(password) + "&shopName=" + encodeURIComponent(shopURL), error).done(
				function(data, statusCode) {

				});

	} else {
		alert(text);
	}
}

function strStartsWith(str, prefix) {
	return str.indexOf(prefix) === 0;
}

function signupcallbackajax(uri, error) {

	var request = {
		url : uri,
		contentType : "application/json",
		accepts : "application/json",
		cache : false,
		dataType : 'jsonp',
		data : 'jsonp',
		success : function(json) {
			if (json.status != 200) {
				alert(error+" -> "+json.message+"("+json.status+")");
			} else {
				var email = document.getElementById("signupEmail").innerHTML;
				var password = document.getElementById("signupPassword").value;
		
				window.location.replace(window.location.href
						+ "&action=configured&heimdalapm_email=" + encodeURIComponent(email)
						+ "&heimdalapm_password=" +encodeURIComponent(password)
						+ "&heimdalapm_customer=" + encodeURIComponent(json.customerId)
						+ "&heimdalapm_shop=" + encodeURIComponent(json.shops[0].shopId));
			}
		},
		error : function(e) {
			alert(error);

		}
	};

	return jQuery.ajax(request);
};

function addShop(email,customerId, password, adviseShort, adviseStart, adviseLocalhost,adviseTerms, error) {

	var shopName = document.getElementById("addShopShopName").value;
	var shopURL = document.getElementById("addShopUrl").value;
	
	var send = true;
	var text = "";

	if (shopName.length < 6) {
		text = adviseShort;
		send = false;

	}

	if (!strStartsWith(shopURL, 'http')) {
		text = text + adviseStart;
		send = false;

	}

	if ((shopURL.indexOf("localhost") > -1) || (shopURL.indexOf("127.0.0.1") > -1)) {
		if (!said) {
			text = adviseLocalhost;
			send = false;
		}
		said = true;
	}
	
	if (!document.getElementById("signupTerms").checked) {
		text = text + adviseTerms;
		send = false;

	}

	if (send) {
		addShopCallbackajax(
				"https://vikinguard.com/heimdalapp/api/external/customer/"+encodeURIComponent(customerId)+"/shop?"
						+ "&mail=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password) 
						+ "&shopName=" + encodeURIComponent(shopName) + "&shopUrl=" + encodeURIComponent(shopURL)
					, error).done(
				function(data, statusCode) {

				});

	} else {
		alert(text);
	}
}

function addShopCallbackajax(uri, error) {
	var request = {
		url : uri,
		contentType : "application/json",
		accepts : "application/json",
		cache : false,
		dataType : 'jsonp',
		data : 'jsonp',
		success : function(json) {
			if ( json.status != 200) {
				alert(error+" -> "+json.message+"("+json.status+")");
			} else {
			
				window.location.replace(window.location.href
						+ "&action=configured"
						+ "&heimdalapm_customer=" + encodeURIComponent(json.customerId)
						+ "&heimdalapm_shop=" + encodeURIComponent(json.id));
			}
		},
		error : function(e) {
			alert(error);

		}
	};

	return jQuery.ajax(request);
};

