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
        .wrap { 
            max-width: 950px;
            margin: 0 auto;
            padding: 0 75px;
            padding-top: 5px;
        }
        .inner {
            display: table;
            width: 100%;
        }
        .left { 
            display: table-cell; 
            text-align: left; 
            font-size: 10.5px;
        }
        .left p {
            line-height: 1.5;
            margin: 0;
        }
      
    </style>
</head>
<body onload="subst()">
    <div class="wrap">
        <div class="inner">
            <div class="left">
                <p><strong>EASYDO Digital Technologies S.R.L</strong></p>
                <p><strong>PaaS Solutions / Cyber Security SOC / API / Data Governance / AI Automation / Application and Infrastructure Services</strong></p>
                <p>support@easydo.co / sales@easydo.co / finance@easydo.co  Tel:+40/(0)21 310 12 94 - Fax: +40/(0)21 311 04 07</p>
                <p>Fiscal code: RO39799672, VAT nr: RO39799672, Bucharest Trade Register nr: J40/12250/2018</p>
            </div>
            <div style="position: fixed; right: 15px; top: 65px; font-size: 13px;">
                <span style="color: #e20000;">Page</span> <span id="page" style="color: #e20000;">Page</span> / <span id="topage"></span>
            </div>

        </div>
    </div>

    <script>
        function subst() {
            var vars = {};
            document.location.search.substring(1).split('&').forEach(function(kv) {
                var pair = kv.split('=');
                vars[pair[0]] = decodeURIComponent(pair[1] || '');
            });
            document.getElementById('page').textContent = vars.page || '';
            document.getElementById('topage').textContent = vars.topage || '';
        }
    </script>
</body>
</html>