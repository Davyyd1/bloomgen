<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 0;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .col-left   { width: 73px;  vertical-align: middle; }
        .col-center { width: 790px; vertical-align: middle; padding-top: 0; padding-top:30px}
        .col-right  { width: 110px;  vertical-align: middle; text-align: center; font-size: 14px; white-space: nowrap; padding-right: 5px; padding-top:30px; font-weight: bold;}

        .col-left img {
            display: block;
        }
        .col-center p {
            line-height: 1.5;
            margin: 0;
        }
    </style>
</head>
<body onload="subst()">
    <table class="footer-table">
        <tr>
            <!-- Page 1 left logo -->
            <td class="col-left" id="page-1">
                <img src="{{ asset('images/logo4.png') }}" width="60" height="70" style="position:absolute; left:-35px; top:30px;">
                <img src="{{ asset('images/logo5.png') }}" width="35" height="35" style="position:absolute; left:28px; top:38px;">
                <img src="{{ asset('images/logo5.png') }}" width="40" height="40" style="position:absolute; top:82px; left:20px;">
            </td>

            <!-- Page other left logo -->
             <td class="col-left" id="page-other">
                <img src="{{ asset('images/logo4.png') }}" width="60" height="70" style="position:absolute; left:-35px; top:30px;">
                <img src="{{ asset('images/logo5.png') }}" width="35" height="35" style="position:absolute; left:28px; top:30px;">
                <img src="{{ asset('images/logo5.png') }}" width="40" height="40" style="position:absolute; top:67px; left:27px;">
            </td>

            <td class="col-center">
                <p><strong>EASYDO Digital Technologies S.R.L</strong></p>
                <p><strong>PaaS Solutions / Cyber Security SOC / API / Data Governance / AI Automation / Application and Infrastructure Services</strong></p>
                <p>support@easydo.co / sales@easydo.co / finance@easydo.co Tel:+40/(0)21 310 12 94 - Fax: +40/(0)21 311 04 07</p>
                <p>Fiscal code: RO39799672, VAT nr: RO39799672, Bucharest Trade Register nr: J40/12250/2018</p>
            </td>

            <td class="col-right">
                <span style="color: #e20000;">Page </span><span id="page" style="color: #e20000;"></span><span> / </span><span id="topage"></span>
            </td>
        </tr>
    </table>

    <script>
        function subst() {
            var vars = {};
            document.location.search.substring(1).split('&').forEach(function(kv) {
                var pair = kv.split('=');
                vars[pair[0]] = decodeURIComponent(pair[1] || '');
            });
            
            var page = parseInt(vars.page || '1');
            
            document.getElementById('page').textContent = vars.page || '';
            document.getElementById('topage').textContent = vars.topage || '';
            
            // show different footer based on page
            if (page === 1) {
                document.getElementById('page-1').style.display = 'table';
                document.getElementById('page-other').style.display = 'none';
            } else {
                document.getElementById('page-1').style.display = 'none';
                document.getElementById('page-other').style.display = 'table';
            }
        }
    </script>
</body>
</html>