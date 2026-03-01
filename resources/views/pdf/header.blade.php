{{-- resources/views/pdf/header.blade.php --}}
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }

            body {
                font-family: Arial, sans-serif;
                font-size: 17px;
                color: #000;
                line-height: 1.4;
            }

            .main-layout {
                width: 100%;
                border-collapse: collapse;
            }

            .col-left   { width: 80px;  vertical-align: top; }
            .col-center { width: 790px; vertical-align: top; }
            .col-right  { width: 80px;  vertical-align: top; text-align: right; }

            .header-inner {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0;
            }
            .header-inner td { padding: 0; vertical-align: middle; }

            .header-name  { font-size: 36px; color: #8f8f8f; font-weight: bold; }
            .header-title { font-size: 28px; color: #232743; font-weight: bold; padding-right: 20px; }
        </style>
    </head>
    <body onload="subst()">
        <header>
        <table class="main-layout">
            <tr>
                <!-- Page 1 header -->
                <td class="col-left"></td>

                <td class="col-center" id="col_center_page_1">
                    <table class="header-inner" style="height: 220px;">
                        <tr>
                            <td style="vertical-align: top;">
                                <img src="{{ asset('images/logoEDO.png') }}" width="200" height="130">
                            </td>
                            <td style="vertical-align: bottom; text-align: right; padding-bottom: 10px;">
                                <div style="display: inline-block; text-align: center;">
                                    <div class="header-name">{{ $name ?? '' }}</div>
                                    <div class="header-title" style="margin-top: 50px;">{{ $title ?? '' }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td class="col-right" id="col_right_page_1">
                    <img src="{{ asset('images/logoEDO2.png') }}" width="120" height="150">
                </td>


                <!-- Page other header -->
                <td class="col-center" id="col_center_page_other">
                    <table class="header-inner" style="height: 220px; vertical-align: top;">
                        <tr>
                            <td style="vertical-align: top; padding-top: 5px;">
                                <img src="{{ asset('images/logo_other_page.png') }}" width="50" height="50">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </header>

        <script>
            function subst() {
                var vars = {};
                document.location.search.substring(1).split('&').forEach(function(kv) {
                    var pair = kv.split('=');
                    vars[pair[0]] = decodeURIComponent(pair[1] || '');
                });
                
                var page = parseInt(vars.page || '1');
                
                if (page === 1) {
                    document.getElementById('col_center_page_1').style.display     = 'table-cell';
                    document.getElementById('col_right_page_1').style.display      = 'table-cell';
                    document.getElementById('col_center_page_other').style.display = 'none';
                } else {
                    document.getElementById('col_center_page_1').style.display     = 'none';
                    document.getElementById('col_right_page_1').style.display      = 'none';
                    document.getElementById('col_center_page_other').style.display = 'table-cell';
                    document.getElementById('col_center_page_other').style.marginTop = '-30mm';
                }
            }
        </script>
    </body>
</html>