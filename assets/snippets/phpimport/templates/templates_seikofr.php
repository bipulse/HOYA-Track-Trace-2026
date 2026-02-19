<?php



?>
<!DOCTYPE html>
<html lang="{html-lang}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="https://services.seikovision.com/assets/snippets/phpimport/templates/" />
    <title>{subject}</title>
    <style>
        @font-face {
            font-family: 'DrescherGrotesk BT DemiBold';
            src: url('font/DrescherGrotesk_BT_DemiBold.woff2') format('woff2'),
                url('font/DrescherGrotesk_BT_DemiBold.woff') format('woff'),
                url('font/DrescherGrotesk_BT_DemiBold.ttf') format('truetype'),
                url('font/DrescherGrotesk_BT_DemiBold.svg') format('svg');
        }

        @font-face {
            font-family: 'DrescherGrotesk BT Roman';
            src: url('font/DrescherGrotesk_BT_Roman.woff2') format('woff2'),
                url('font/DrescherGrotesk_BT_Roman.woff') format('woff'),
                url('font/DrescherGrotesk_BT_Roman.ttf') format('truetype'),
                url('font/DrescherGrotesk_BT_Roman.svg') format('svg');
        }

        @font-face {
            font-family: 'DrescherGrotesk BT SemiBold';
            src: url('font/DrescherGrotesk_BT_SemiBold.woff2') format('woff2'),
                url('font/DrescherGrotesk_BT_SemiBold.woff') format('woff'),
                url('font/DrescherGrotesk_BT_SemiBold.ttf') format('truetype'),
                url('font/DrescherGrotesk_BT_SemiBold.svg') format('svg');
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "DrescherGrotesk BT Roman", Arial, sans-serif;
            background-color: gray;
            font-size: 20px;
            line-height: 140%;
        }

        b {
            font-family: "DrescherGrotesk BT SemiBold", Arial, sans-serif;

        }



        .container {
            max-width: 1140px;
            margin: 0 auto;
        }

        .header {
            background-color: black;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header img {
            width: auto;
            height: 80px;
        }

        .image-section img {
            width: 100%;
            height: 570px;
            display: block;
            /* Removes white space below image */
            object-fit: cover;
        }

        .two-block-section {
            display: flex;
        }

        .two-block-section.intro .block1 {
            display: flex;
            justify-content: center;
            align-items: start;
            flex-direction: column;
            /* If your content is vertical, use this to ensure it's centered correctly. */
        }

        


        .block1,
        .block2 {
            display: flex;
            align-items: start;
            justify-content: start;
            padding: 80px;
            word-wrap: break-word;

            /* Wrap content */
        }

        .block1 {
            width: 400px;
            background-color: #D28C00;
            flex-direction: column;
        }

        .block2 {
            width: 740px;
            background-color: #DCDCDC;
        }

        .groups-section .block2 {
            padding: 40px 40px 0px 20px;
        }

        .groups-section .block1 {
            padding: 40px 80px 0px 80px;
        }

        .groups-section .block1 b {
            margin-bottom: 10px;
        }

        .group-table {
            margin-bottom: 20px;
            width: 100%;
        }

        .group-table table {
            width: 100%;
            border-collapse: collapse;
            border: 0px solid #ddd;
        }

        .group-table th {
            background-color: #f2f2f2;
            padding: 10px 15px;
            border-right: 4px solid #fff;
            font-size: 14px;
            line-height: 100%;
            text-align: left;
        }

        .group-table td {
            padding: 15px;
            border-bottom: 2px solid #D28C00;
            font-size: 14px;
            line-height: 130%;
        }

        .group-table td:first-child {
            width: 25%;
        }

        .group-table td:nth-child(2) {
            width:45%;
        }

        .group-table td:nth-child(4),
        .group-table td:nth-child(5) {
            width: 15%;
        }

        .mobile {
            display: none;
        }

        .desktop {
            display: table-cell;
        }

        .services .block2 {
            display: flex;
            justify-content: space-between;
            /* This will distribute the images evenly with space between them. */
            align-items: center;
            /* This centers the images vertically. */
        }

        .services .block2 a {
            width: calc(33.33% - 20px);
            /* This assumes you want a gap of 20px. Adjust the 20px value as needed. */
            height: auto;
            /* This ensures the image maintains its aspect ratio. */
        }
        .services .block2 img {
            width: 100%;
        }
        .footer {
            background-color: black;
            color: white;
            padding: 20px 0;
            /* Add vertical padding for some space. */
            text-align: center;
            /* To center the content. */
            font-size: 13px;
            padding: 100px 0 200px;

            background-image: url('images/quote-left.png'), url('images/quote-right.png');
            background-repeat: no-repeat, no-repeat;
            /* Ensuring both images do not repeat */
            background-position: left top, right bottom;
            /* Positioning the images */
            background-size: auto, auto;
            /* This ensures the images are their natural sizes on larger screens */

        }

        .footer .pipe {
            color: #D28C00;
            margin: 0 10px;
            /* Optional: some horizontal spacing around the pipe symbol. */
        }

        .footer a{
            color: #D28C00;
        }


        @media (max-width: 768px) {

            .header {
                background-color: black;
                height: 120px;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .header img {
                width: auto;
                height: 50px;
            }

            .image-section img {
                width: 100%;
                height: 370px;
                display: block;
                /* Removes white space below image */
                object-fit: cover;
            }

            .intro .block1 {
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .two-block-section {
                flex-direction: column;
            }

            .two-block-section.intro .block1 {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

            .block1,
            .block2 {
                width: 100%;
                padding: 30px;
            }

            .image-section img {
                height: auto;
            }

            .container {
                width: 100%;
            }

            .groups-section .block2 {
                padding: 40px 40px 0px 30px;
            }

            .groups-section .block1 {
                padding: 40px 80px 0px 30px;
            }

            .group-table td {
                padding: 10px;
                border-bottom: 1px solid #D28C00;
                font-size: 14px;
                line-height: 130%;
            }


            .group-table td:nth-child(3) {
                width: 70%;
            }

            .group-table td:nth-child(4) {
                width: 15%;
            }

            .group-table td:nth-child(5) {
                width: 15%;
            }

            .group-table th {
                font-size: 12px;
            }

            .group-table td {
                font-size: 12px;
            }

            .mobile {
                display: table-cell;
            }

            .desktop {
                display: none;
            }

            .services .block1 {
                padding: 10px;
            }

            .footer {
                background-size: 25%, 25%;
                /* This reduces the background images to 1/4 size */
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="images/seiko-logo.png" alt="Seiko Logo">
        </div>
        <div class="image-section">
            <img src="images/header-bg.jpg" alt="Header Background">
        </div>
        <div class="two-block-section intro">
            <div class="block1">{customeraddress}
               
            </div>
            <div class="block2">{seiko-intro}</div>
        </div>
        <!-- ... previous sections ... -->

        <div class="groups-section">
            <!-- 5 groups -->
            <div class="two-block-section">
                <!-- First Group -->
                <!-- Text block -->
                <div class="block1" style="background-color: white;">
                    <b>{new-orders}</b>

                    {new-orders-text}
                </div>
                <!-- Table block -->
                <div class="block2" style="background-color: white;">
                    <div class="group-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="desktop">{order-ref}</th>
                                    <th class="desktop">{design}</th>
                                    <th class="mobile" style="display:none">{order-ref} / {design}</th>
                                    <th>{planned-date}</th>
                                    <th>{order-date}</th>
                                </tr>
                            </thead>
                            <tbody>
                               

                                {block0}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Repeat the above structure for groups 2 to 5 -->
            <!-- ... -->
        </div>
        
        <div class="groups-section">
            <!-- 5 groups -->
            <div class="two-block-section">
                <!-- First Group -->
                <!-- Text block -->
                <div class="block1" style="background-color: white;">
                    <b>{new-planned-delivery-dates}</b>

                    {new-planned-delivery-dates-text}
                </div>
                <!-- Table block -->
                <div class="block2" style="background-color: white;">
                    <div class="group-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="desktop">{order-ref}</th>
                                    <th class="desktop">{design}</th>
                                    <th class="mobile" style="display:none">{order-ref} / {design}</th>
                                    <th>{planned-date}</th>
                                    <th>{order-date}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {block1}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Repeat the above structure for groups 2 to 5 -->
            <!-- ... -->
        </div>
        <div class="groups-section">
            <!-- 5 groups -->
            <div class="two-block-section">
                <!-- First Group -->
                <!-- Text block -->
                <div class="block1" style="background-color: white;">
                    <b>{deliveries-in-the-coming-3-days}</b>

                    {deliveries-in-the-coming-3-days-text}
                </div>
                <!-- Table block -->
                <div class="block2" style="background-color: white;">
                    <div class="group-table">
                        <table style="width:100%">
                            <thead>
                                <tr>
                                    <th class="desktop">{order-ref}</th>
                                    <th class="desktop">{design}</th>
                                    <th class="mobile" style="display:none">{order-ref} / {design}</th>
                                    <th>{planned-date}</th>
                                    <th>{order-date}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {block2}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Repeat the above structure for groups 2 to 5 -->
            <!-- ... -->
        </div>
        <div class="groups-section">
            <!-- 5 groups -->
            <div class="two-block-section">
                <!-- First Group -->
                <!-- Text block -->
                <div class="block1" style="background-color: white;">
                    <b>{all-open-orders}</b>

                    {all-open-orders-text}
                </div>
                <!-- Table block -->
                <div class="block2" style="background-color: white; padding-bottom: 80px;">
                    <div class="group-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="desktop">{order-ref}</th>
                                    <th class="desktop">{design}</th>
                                    <th class="mobile" style="display:none">{order-ref} / {design}</th>
                                    <th>{planned-date}</th>
                                    <th>{order-date}</th>
                                </tr>
                            </thead>
                            <tbody>
                            {block3}
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Repeat the above structure for groups 2 to 5 -->
            <!-- ... -->
        </div>

        <div class="two-block-section intro services">
            <div class="block1">
                <b>{other-services}</b>

            </div>
            <div class="block2 ">
                <a href="https://fr.seikoxtranet.com" target="_blank"><img src="images/SEIKO XTRANET.png" /></a>
                <a href="https://www.seikovisionpro.com" target="_blank"><img src="images/SEIKO PRO.png" /></a>
                <a href="https://www.seikovision.com/fr" target="_blank"><img src="images/SEIKO VISION.png" /></a>
            </div>
        </div>
        <div class="footer">
            {seiko-footer-address}<br><br>
            {contact-tel-label} : {contact-tel} <span class="pipe">|</span> {contact-email-label} : <a href="mailto:{contact-email}">{contact-email}</a>
        </div>


        <!-- ... rest of the HTML ... -->

    </div>
</body>

</html>