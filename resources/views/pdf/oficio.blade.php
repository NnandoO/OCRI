<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Oficio</title>
    <style>
        @page {
            margin: 100px 50px 80px 70px;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 70px;
            text-align: center;
        }
        footer {
            position: fixed; 
            bottom: -60px; 
            left: 0px; 
            right: 0px;
            height: 50px; 
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
        }
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .logo-left {
            width: 80px;
        }
        .logo-right {
            width: 80px;
        }
        .header-text {
            text-align: center;
        }
        .univ-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        .office-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        .year-name {
            font-size: 11px;
            font-style: italic;
            margin-top: 5px;
        }
        .separator {
            border-top: 1px solid #000;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .date {
            text-align: right;
            margin-bottom: 15px;
        }
        .oficio-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .content {
            text-align: justify;
        }
        .footer-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }
        .footer-text {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <header>
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 80px; text-align: left;">
                    @if(file_exists(public_path('Logo-UNCP.png')))
                        <img src="{{ public_path('Logo-UNCP.png') }}" class="logo-left">
                    @endif
                </td>
                <td class="header-text">
                    <p class="univ-name">UNIVERSIDAD NACIONAL DEL CENTRO DEL PERÚ</p>
                    <p class="office-name">OFICINA DE COOPERACIÓN Y RELACIONES INTERNACIONALES</p>
                    <p class="year-name">"{{ $yearName }}"</p>
                </td>
                <td style="width: 80px; text-align: right;">
                    @if(file_exists(public_path('ocri_logo.png')))
                        <img src="{{ public_path('ocri_logo.png') }}" class="logo-right">
                    @else
                        <div style="border: 1px solid #006699; padding: 10px; text-align: center; font-size: 10px; font-weight: bold; color: #006699;">
                            O.C.R.I.<br><span style="font-weight: normal; font-size: 9px;">UNCP</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
        <div class="separator"></div>
    </header>

    <footer>
        <div class="footer-line"></div>
        <div class="footer-text">c.c. Archivo</div>
    </footer>

    <main>
        <div class="date">
            Huancayo, {{ $dateText }}
        </div>
        
        <div class="oficio-number">
            OFICIO N° {{ $oficio->oficio_number }}
        </div>
        
        <div class="content">
            {!! $oficio->body_html !!}
        </div>
        
        <br><br><br>
        @if(file_exists(public_path('firma.png')))
            <div style="text-align: center; margin-top: 40px;">
                <img src="{{ public_path('firma.png') }}" style="width: 180px;">
            </div>
        @endif
    </main>
</body>
</html>
