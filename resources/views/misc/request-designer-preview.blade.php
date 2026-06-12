<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request a Designer Visit – Cape Cod Australia Preview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /*
         * Cape Cod Australia preview page
         * ------------------------------------------------------------
         * This Blade file is a client-preview wrapper page.
         *
         * It recreates the public Request a Designer Visit page structure:
         * - top enquiry/social bar
         * - logo + 60-year header
         * - navigation
         * - intro content sections
         * - the actual Laravel request form loaded by iframe
         * - footer
         *
         * The iframe below loads the real form route:
         * /wp/request-designer
         */

        :root {
            --cape-purple: #7b0046;
            --cape-green: #3f5f5a;
            --cape-green-soft: #738d87;
            --cape-text: #333333;
            --cape-muted: #555555;
            --cape-line: #e5e5e5;
            --cape-max: 1110px;
            --cape-font: Arial, Helvetica, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: var(--cape-font);
            color: var(--cape-text);
            background: #ffffff;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        /*
         * Very top green bar.
         */
        .cape-topbar {
            width: 100%;
            background: var(--cape-green);
            color: #ffffff;
            min-height: 34px;
            font-size: 13px;
        }

        .cape-topbar-inner {
            max-width: var(--cape-max);
            margin: 0 auto;
            min-height: 34px;
            padding: 0 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cape-enquiry {
            color: #ffffff;
            font-weight: 700;
            line-height: 34px;
            text-transform: lowercase;
        }

        .cape-social {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
        }

        .cape-social a {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
        }

        /*
         * Header with logo, anniversary graphic and nav.
         */
        .cape-header {
            background: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, .08);
        }

        .cape-header-inner {
            max-width: var(--cape-max);
            margin: 0 auto;
            padding: 16px 18px 0;
            display: grid;
            grid-template-columns: 220px 1fr;
            column-gap: 20px;
            align-items: start;
        }

        .cape-logo {
            display: block;
            padding-top: 7px;
        }

        .cape-logo img {
            width: 170px;
            display: block;
        }

        .cape-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            min-width: 0;
        }

        .cape-anniversary {
            width: 100%;
            text-align: right;
            margin-bottom: 7px;
        }

        .cape-anniversary img {
            width: 455px;
            max-width: 100%;
            display: inline-block;
        }

        .cape-nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0;
            width: 100%;
            min-height: 45px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #2f2f2f;
        }

        .cape-nav > a,
        .cape-nav > span {
            display: inline-flex;
            align-items: center;
            min-height: 45px;
            padding: 0 13px;
            color: #333333;
            white-space: nowrap;
        }

        .cape-nav > a:hover,
        .cape-nav > span:hover,
        .cape-nav > .active {
            color: var(--cape-purple);
        }

        /*
         * Mobile-style image/nav area. On desktop this is mostly hidden;
         * it helps the page keep a similar feel at smaller widths.
         */
        .cape-mobile-brand {
            display: none;
            max-width: var(--cape-max);
            margin: 0 auto;
            padding: 18px;
        }

        .cape-mobile-brand img {
            width: 180px;
        }

        .cape-mobile-nav {
            display: none;
            max-width: var(--cape-max);
            margin: 0 auto;
            padding: 0 18px 18px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--cape-green);
        }

        .cape-mobile-nav a {
            display: inline-block;
            margin-right: 18px;
            margin-bottom: 8px;
        }

        /*
         * Page body.
         * The original form area has a light interior photo washed out behind it.
         */
        .cape-page {
            /*
             * Same soft interior/wallpaper background used behind the embedded form.
             * Keep the overlay light so text remains readable.
             */
            background: linear-gradient(rgba(255, 255, 255, .84), rgba(255, 255, 255, .90)),
            url('/images/designer-visit-bg.jpg') center top / cover no-repeat;
            background-attachment: fixed;
        }

        /*
         * Main two-column layout:
         * - left column = page content
         * - right column = actual iframe form
         */
        .cape-content {
            max-width: var(--cape-max);
            margin: 0 auto;
            padding: 42px 18px 54px;
        }

        .cape-two-column-layout {
            display: grid;
            grid-template-columns: minmax(310px, 0.82fr) minmax(500px, 1.18fr);
            gap: 44px;
            align-items: start;
        }

        /*
         * Left content column.
         */
        .cape-intro {
            width: 100%;
            padding: 0 0 20px;
        }

        .cape-design-link {
            display: inline-block;
            margin-bottom: 18px;
            color: var(--cape-purple);
            font-size: 15px;
            line-height: 1.5;
        }

        .cape-design-link:hover {
            text-decoration: underline;
        }

        .cape-intro h2 {
            margin: 0 0 14px;
            color: var(--cape-green);
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
        }

        .cape-intro p {
            margin: 0 0 24px;
            color: #4f4f4f;
            font-size: 16px;
            line-height: 1.85;
            font-weight: 400;
        }

        .cape-intro-columns {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            align-items: start;
            margin-top: 4px;
        }

        .cape-why ul {
            margin: 0;
            padding: 0;
            list-style-position: inside;
            color: #4f4f4f;
            font-size: 16px;
            line-height: 1.9;
        }

        .cape-why li {
            margin: 0;
        }

        /*
         * The real form iframe.
         * The embedded form sends a postMessage with its height, and the script
         * at the bottom updates this iframe height automatically.
         */
        .cape-form-holder {
            width: 100%;
            padding: 0;
        }

        .cape-form-frame {
            display: block;
            width: 100%;
            min-height: 900px;
            border: 0;
            overflow: hidden;
            background: transparent;
        }

        /*
         * Footer.
         */
        .cape-footer {
            background: #ffffff;
            border-top: 1px solid var(--cape-line);
        }

        .cape-footer-inner {
            max-width: var(--cape-max);
            margin: 0 auto;
            padding: 26px 18px 30px;
            text-align: center;
        }

        .cape-footer-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 22px;
            margin-bottom: 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--cape-green);
            text-transform: uppercase;
        }

        .cape-footer-copy {
            color: #777777;
            font-size: 13px;
            line-height: 1.7;
        }

        @media (max-width: 900px) {
            .cape-header-inner {
                grid-template-columns: 1fr;
                padding-bottom: 14px;
            }

            .cape-logo {
                display: none;
            }

            .cape-header-right {
                align-items: flex-start;
            }

            .cape-anniversary {
                display: none;
            }

            .cape-nav {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .cape-nav > a,
            .cape-nav > span {
                padding: 0 14px 0 0;
            }

            .cape-mobile-brand,
            .cape-mobile-nav {
                display: block;
            }

            .cape-two-column-layout,
            .cape-intro-columns {
                grid-template-columns: 1fr;
                gap: 26px;
            }
        }

        @media (max-width: 560px) {
            .cape-topbar-inner {
                padding: 0 15px;
            }

            .cape-content,
            .cape-form-holder,
            .cape-footer-inner,
            .cape-mobile-brand,
            .cape-mobile-nav {
                padding-left: 15px;
                padding-right: 15px;
            }

            .cape-social {
                display: none;
            }

            .cape-intro h2 {
                font-size: 26px;
            }

            .cape-intro p,
            .cape-why ul {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

<div class="cape-topbar">
    <div class="cape-topbar-inner">
        <a class="cape-enquiry" href="#request-designer-form">make an enquiry</a>

        <div class="cape-social" aria-label="Social links">
            <a href="https://www.facebook.com/capecodaustralia" aria-label="Facebook">f</a>
            <a href="https://www.instagram.com/capecodaustralia/" aria-label="Instagram">◎</a>
        </div>
    </div>
</div>

<header class="cape-header">
    <div class="cape-header-inner">
        <a class="cape-logo" href="#" aria-label="Cape Cod Australia">
            <img
                    src="https://www.capecod.com.au/wp-content/uploads/2024/07/My-Life.-My-Home.-Logo-e1741744241578.png"
                    alt="Cape Cod Australia">
        </a>

        <div class="cape-header-right">
            <div class="cape-anniversary">
                <img
                        src="https://www.capecod.com.au/wp-content/uploads/2026/01/60-Web-Header-Signature.png"
                        alt="Celebrating 60 years">
            </div>

            <nav class="cape-nav" aria-label="Main navigation">
                <a href="#">Home</a>
                <span>About</span>
                <span>Projects</span>
                <a href="#">FAQs</a>
                <a href="#" class="active">Contact</a>
            </nav>
        </div>
    </div>
</header>

<div class="cape-mobile-brand">
    <img
            src="https://www.capecod.com.au/wp-content/uploads/2025/05/Website-Header-1.png"
            alt="Cape Cod Australia">
</div>

<nav class="cape-mobile-nav" aria-label="Mobile navigation">
    <a href="#">Home</a>
    <a href="#">Contact Us</a>
    <a href="#">About Us</a>
    <a href="#">Projects</a>
</nav>

<div class="cape-page" style=" background: url('https://www.capecod.com.au/wp-content/uploads/2020/03/Pg1-Background-edited.jpg') center top / cover no-repeat; ">
    <main>
        <section class="cape-content" aria-label="Request a Designer Visit preview">
            <div class="cape-two-column-layout" style="background-color: rgba(49, 79, 71, 0.9);">
                <div id="intro-text" class="hidden-xs hidden-sm col-md-6 green-fade form-col" style="padding: 30px">
                    <div class="row">
                        <div class="col-xs-12" style="color: #fff">
                            <h2 style="color: #ffffff; font-family: 'PT Sans',Helvetica,Arial,Lucida,sans-serif;">Our Design Team</h2>
                            <p style="font-size: 14px">Cape Cod Australia’s design team includes architects and
                                professional design consultants with many years’ experience
                                in the Home Additions industry. Our expert designers are
                                passionate about meeting your requirements and budget to
                                bring your dream home to life.</p>
                            <h2 style="color: #ffffff; font-family: 'PT Sans',Helvetica,Arial,Lucida,sans-serif;">Your Renovation Goals</h2>
                            <p style="font-size: 14px">We design homes throughout the greater Sydney region in all
                                styles, and work carefully to integrate the new extension with
                                your existing home. If you are considering adding a first floor
                                addition or a ground floor extension to your home, we can
                                provide realistic advice on design, costs and options – free of
                                charge and with no obligation to proceed.</p>
                            <h2 style="color: #ffffff; font-family: 'PT Sans',Helvetica,Arial,Lucida,sans-serif;">Why Cape Cod?</h2>
                            <ul style="font-size: 14px">
                                <li>Fixed Price Quotation</li>
                                <li>Guaranteed Construction Time</li>
                                <li>Extended Maintenance Period</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="cape-form-holder" id="request-designer-form" aria-label="Request a Designer Visit form">
                    <iframe
                            id="request-designer-frame"
                            class="cape-form-frame"
                            src="/wp/request-designer"
                            title="Request a Designer Visit"
                            loading="lazy"
                            scrolling="no">
                    </iframe>
                </div>
            </div>
        </section>
    </main>
</div>

<footer class="cape-footer">
    <div class="cape-footer-inner">
        <nav class="cape-footer-nav" aria-label="Footer navigation">
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">Projects</a>
            <a href="#">FAQs</a>
            <a href="#">Contact</a>
        </nav>

        <div class="cape-footer-copy">
            © Copyright {{ date('Y') }} Cape Cod Australia Pty Ltd. All rights reserved.
            <a href="#">Privacy Policy.</a>
        </div>
    </div>
</footer>

<script>
    /*
     * The embedded Laravel form posts its height using:
     * { type: 'request-designer-height', height: document.body.scrollHeight }
     *
     * This makes the iframe resize when the user moves from Part 1 to Part 2.
     */
    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'request-designer-height') {
            return;
        }

        const frame = document.getElementById('request-designer-frame');

        if (!frame) {
            return;
        }

        frame.style.height = Math.max(900, Number(event.data.height || 0)) + 'px';
    });
</script>

</body>
</html>


