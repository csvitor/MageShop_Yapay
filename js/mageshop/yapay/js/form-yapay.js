function apenasLetras(e, t) {
    try {
        if (window.event) {
            var charCode = window.event.keyCode;
        } else if (e) {
            var charCode = e.which;
        } else {
            return true;
        }
        if (
            (charCode > 64 && charCode < 91) ||
            (charCode > 96 && charCode < 123) ||
            (charCode > 191 && charCode <= 255) ||
            (charCode == 32) // letras com acentos
        ) {
            document.getElementById("traycheckoutapi_cc_owner").style.borderColor = "silver";
            return true;

        } else {
            document.getElementById("traycheckoutapi_cc_owner").style.borderColor = "red";
            return false;
        }
    } catch (err) {
        console.log(err.Description);
    }
}

function identifyCreditCardTc(ccNumber) {

    regexElo = getEloPattern(ccNumber);

    var eloRE = /^((((457393)|(431274)|(627780)|(636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})$/;
    var elo2RE = /^(4011(78|79)|43(1274|8935)|45(1416|7393|763(1|2))|50(4175|6699|67[0-7][0-9]|9000)|50(9[0-9][0-9][0-9])|627780|63(6297|6368)|650(03([^4])|04([0-9])|05(0|1)|05([7-9])|06([0-9])|07([0-9])|08([0-9])|4([0-3][0-9]|8[5-9]|9[0-9])|5([0-9][0-9]|3[0-8])|9([0-6][0-9]|7[0-8])|7([0-2][0-9])|541|700|720|727|901)|65165([2-9])|6516([6-7][0-9])|65500([0-9])|6550([0-5][0-9])|655021|65505([6-7])|6516([8-9][0-9])|65170([0-4]))$/;
    var elo3RE = regexElo;
    var visaRE = /^4[0-9]{12}(?:[0-9]{3})?$/;
    var masterRE = /^(5[1-5]|677189)|^(222[1-9]|2[3-6]\d{2}|27[0-1]\d|2720)/;
    var master2RE = /^((5[1-5][0-9]{14})$|^(2(2(?=([2-9]{1}[1-9]{1}))|7(?=[0-2]{1}0)|[3-6](?=[0-9])))[0-9]{14})$/;
    var amexRE = /^3[47][0-9]{13}$/;
    var discoverRE = /^6(?:011|5[0-9]{2})[0-9]{12}$/;
    var hiperRE = /^(606282\d{10}(\d{3})?)|^(3841\d{15})$/;
    var hiperItauRE = /^(637095)\d{0,10}$/;
    var dinersRE = /^((30(1|5))|(36|38)\d{1})\d{11}/;
    var jcbRE = /^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$/;
    var auraRE = /^50[0-9]{17}$/;
    document.getElementById('yapay_creditcardpayment_cc_payment_code').value = "";

    // try { document.getElementById('tcPaymentFlag3').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag2').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag5').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag15').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag20').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag18').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag19').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    // try { document.getElementById('tcPaymentFlag25').className = 'tcPaymentFlag'; } catch (err) { console.debug(err.message); }
    if (eloRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '16';
        //document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } if (elo2RE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '16';
       // document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } if (elo3RE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '16';
        //document.getElementById('tcPaymentFlag16').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (visaRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '3';
        //document.getElementById('tcPaymentFlag3').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (masterRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '4';
        //document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (master2RE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '4';
        //document.getElementById('tcPaymentFlag4').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (amexRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '5';
        //document.getElementById('tcPaymentFlag5').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (discoverRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '15';
        //document.getElementById('tcPaymentFlag15').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (hiperRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '20';
        //document.getElementById('tcPaymentFlag20').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (hiperItauRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '25';
        //document.getElementById('tcPaymentFlag25').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (dinersRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '2';
        //document.getElementById('tcPaymentFlag2').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (jcbRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '19';
        //document.getElementById('tcPaymentFlag19').className = 'tcPaymentFlag tcPaymentFlagSelected';
    } else if (auraRE.test(ccNumber)) {
        document.getElementById('yapay_creditcardpayment_cc_payment_code').value = '18';
        //document.getElementById('tcPaymentFlag18').className = 'tcPaymentFlag tcPaymentFlagSelected';
    }
    var maskCcNumber = ccNumber.substr(0, 4);
    maskCcNumber += (ccNumber.length > 4) ? " " + ccNumber.substr(4, 4) : "";
    maskCcNumber += (ccNumber.length > 8) ? " " + ccNumber.substr(8, 4) : "";
    maskCcNumber += (ccNumber.length > 12) ? " " + ccNumber.substr(12, 4) : "";
    maskCcNumber += (ccNumber.length > 16) ? " " + ccNumber.substr(16, 4) : "";
    return maskCcNumber;
}
function somenteNumeros(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 46 || charCode > 31 && (charCode < 48 || charCode > 57)){
        evt.preventDefault();
        return false;
    }
    return true;
}
function getSplitValues() {

    var pMethod = document.querySelector('#yapay_creditcardpayment_cc_payment_code').value;
    var pathM = document.querySelector('input[id="url_controller"]').value;

    if (pMethod != "") {
        var _split = document.getElementById('yapay_creditcardpayment_cc_split_number');
        var _split_value = document.getElementById('yapay_creditcardpayment_cc_split_number_value');
        _split.innerHTML = "<option value=\"\">Carregando ...</option>";
        var data_file = pathM + "cc/split/method/" + pMethod;
        var http_request = new XMLHttpRequest();
        try {
            http_request = new XMLHttpRequest();
        } catch (e) {
            try {
                http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    console.debug("Your browser broke!");
                    return false;
                }
            }
        }
        http_request.onreadystatechange = function () {
            if (http_request.readyState == 4) {
                var jsonObj = JSON.parse(http_request.responseText);
                _split.innerHTML = "";
                var optionSplit = "<option value=\'\'>Selecione</option>";
                for (var key in jsonObj) {
                    optionSplit += "<option value='" + key + "'>" + jsonObj[key] + "</option>";
                }
                _split.innerHTML = optionSplit;
                _split_value.value = jsonObj[1].replace(/.*R\$/, '').replace(/\,/, '.');
            }
        }
        http_request.open("GET", data_file, true);
        http_request.send();
    }
}


function generateRegexRange(start, end) {
    var regexResult = '';
    var or = '|';
    start = parseInt(start, 10);
    end = parseInt(end, 10);

    if (!(start >= 0 && end > 0)) {
        return false;
    }

    for (var i = start; i <= end; i++) {
        regexResult += '(' + i + ')';
        if (i < end) {
            regexResult += or;
        }
    }

    return regexResult;
}

function getEloPattern(ccNumber) {
    var regexPattern = '^(' + this.generateRegexRange(457631, 457632) + '|';
    regexPattern += this.generateRegexRange(506699, 506778) + '|';
    regexPattern += this.generateRegexRange(509000, 509999) + '|';
    regexPattern += this.generateRegexRange(650031, 650033) + '|';
    regexPattern += this.generateRegexRange(650035, 650051) + '|';
    regexPattern += this.generateRegexRange(650405, 650439) + '|';
    regexPattern += this.generateRegexRange(650485, 650538) + '|';
    regexPattern += this.generateRegexRange(650541, 650598) + '|';
    regexPattern += this.generateRegexRange(650700, 650718) + '|';
    regexPattern += this.generateRegexRange(650720, 650727) + '|';
    regexPattern += this.generateRegexRange(650901, 650978) + '|';
    regexPattern += this.generateRegexRange(651652, 651679) + '|';
    regexPattern += this.generateRegexRange(655000, 655019) + '|';
    regexPattern += this.generateRegexRange(655021, 655058) + ')';
    return new RegExp(regexPattern);
}


Validation.addAllThese([
    [
      "validate-document",
      "Documento inválido. Verifique por favor",
      function (v) {
        const tamDocument = v.length;
        if (tamDocument == 14) {
          v = v.replace(/\D/g, "");
          if (v.toString().length != 11 || /^(\d)\1{10}$/.test(v)) return false;
          var result = true;
          [9, 10].forEach(function (j) {
            var soma = 0,
              r;
            v.split(/(?=)/)
              .splice(0, j)
              .forEach(function (e, i) {
                soma += parseInt(e) * (j + 2 - (i + 1));
              });
            r = soma % 11;
            r = r < 2 ? 0 : 11 - r;
            if (r != v.substring(j, j + 1)) result = false;
          });
          return result;
        } else if (tamDocument == 18) {
          var cnpj = v.trim();
  
          cnpj = cnpj.replace(/\./g, "");
          cnpj = cnpj.replace("-", "");
          cnpj = cnpj.replace("/", "");
          cnpj = cnpj.split("");
  
          var v1 = 0;
          var v2 = 0;
          var aux = false;
  
          for (var i = 1; cnpj.length > i; i++) {
            if (cnpj[i - 1] != cnpj[i]) {
              aux = true;
            }
          }
  
          if (aux == false) {
            return false;
          }
  
          for (var i = 0, p1 = 5, p2 = 13; cnpj.length - 2 > i; i++, p1--, p2--) {
            if (p1 >= 2) {
              v1 += cnpj[i] * p1;
            } else {
              v1 += cnpj[i] * p2;
            }
          }
  
          v1 = v1 % 11;
  
          if (v1 < 2) {
            v1 = 0;
          } else {
            v1 = 11 - v1;
          }
  
          if (v1 != cnpj[12]) {
            return false;
          }
  
          for (var i = 0, p1 = 6, p2 = 14; cnpj.length - 1 > i; i++, p1--, p2--) {
            if (p1 >= 2) {
              v2 += cnpj[i] * p1;
            } else {
              v2 += cnpj[i] * p2;
            }
          }
  
          v2 = v2 % 11;
  
          if (v2 < 2) {
            v2 = 0;
          } else {
            v2 = 11 - v2;
          }
  
          if (v2 != cnpj[13]) {
            return false;
          } else {
            return true;
          }
        } else {
          return false;
        }
      },
    ],
    [
      "validate-date",
      "Data de nascimento inválida. Verifique por favor",
      function (v) {
        date = v;
        var bits = date.split("/");
        var y = bits[2],
          m = bits[1],
          d = bits[0];
  
        var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  
        if ((!(y % 4) && y % 100) || !(y % 400)) {
          daysInMonth[1] = 29;
        }
        return !/\D/.test(String(d)) && d > 0 && d <= daysInMonth[--m];
      },
    ],
  
    [
      "validate-cc-number",
      "Número do cartão inválido. Verifique por favor",
      function (v) {
        cardNumber = v.replace(/[\ ]/g, "");
  
        if (cardNumber.length === 0) return false;
  
        let digit, digits, flag, sum, _i, _len;
        flag = true;
        sum = 0;
        digits = (cardNumber + "").split("").reverse();
        for (_i = 0, _len = digits.length; _i < _len; _i++) {
          digit = digits[_i];
          digit = parseInt(digit, 10);
          if ((flag = !flag)) {
            digit *= 2;
          }
          if (digit > 9) {
            digit -= 9;
          }
          sum += digit;
        }
  
        return sum % 10 === 0;
      },
    ],
  
    [
      "validate-name",
      "Nome inválido. Verifique por favor",
      function (v) {
        if (v == "" || v == " ") {
          return false;
        } else {
          return true;
        }
      },
    ],
  
    [
      "validate-expires_at",
      "Data de vencimento inválida. Verifique por favor",
      function (v) {
        let dtArray = v.split("/");
  
        if (dtArray == null) return false;
  
        var dtMonth = dtArray[0];
        var dtYear = dtArray[1];
  
        if (!Number(dtMonth)) return false;
        if (!Number(dtYear)) return false;
  
        if (dtMonth < 1 || dtMonth > 12) return false;
  
        if (dtYear.length === 3) return false;
  
        if (dtYear.length === 2) dtYear = "20" + dtYear;
  
        if (dtYear < new Date().getFullYear() || dtYear > 2050) return false;
  
        return true;
      },
    ],
  
    [
      "validate-cvv",
      "CVV inválido. Verifique por favor",
      function (v) {
        const i = v.length;
        if (isNaN(v) || v.includes(" ") || i < 3) {
          return false;
        } else {
          return true;
        }
      },
    ],
  
    [
      "validate-installment",
      "Número de parcelas inválida. Verifique por favor",
      function (v) {
        if (v == 0) {
          return false;
        } else {
          return true;
        }
      },
    ],
    [
      "validate-expirymonth",
      "Mês inválido. Verifique por favor",
      function (v) {
        if (v > 0) {
          return true;
        } else {
          return false;
        }
      },
    ],
    [
      "validate-expiryyear",
      "Ano inválido. Verifique por favor",
      function (v) {
        if (v > 0) {
          return true;
        } else {
          return false;
        }
      },
    ],
  ]);