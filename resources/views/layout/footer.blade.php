<!-- BEGIN PRE-FOOTER -->
<div class="page-prefooter">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                @if (Auth::user()->company_id == 41 || Auth::user()->parent_company == 41)
                    <img src="/img/logo-sydneywaste.png" height="100">
                @elseif (Auth::user()->company_id == 198 || Auth::user()->parent_company == 198)
                    <img src="/img/logo-capstone.png" height="100">
                @elseif (Auth::user()->company_id == 210 || Auth::user()->parent_company == 210)
                    <img src="/img/logo-blue-eco.png" height="100">
                @elseif (Auth::user()->company_id == 302 || Auth::user()->parent_company == 302)
                    <img src="/img/logo-shanshel.png" height="100">
                @else
                    <img src="/img/logo-capecod.png" height="100">
                @endif
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                <h2>Contact</h2> {{ Auth::user()->company_id }} - {{ Auth::user()->parent_company }} PP
                @if (in_array(Auth::user()->company_id, [41,198, 210, 302]) || in_array(Auth::user()->parent_company, [41, 198, 210, 302]))
                    <address class="margin-bottom-40">
                        @if (in_array(Auth::user()->company_id, [41,198, 210, 302]))
                            Phone: {{ Auth::user()->company->phone }}<br>
                            Email: <a href="mailto:{{ Auth::user()->company->email }}">{{ Auth::user()->company->email }}</a>
                        @else
                            Phone: {{ Auth::user()->company->reportsTo()->phone }}<br>
                            Email:<a href="mailto:{{ Auth::user()->company->reportsTo()->email }}">{{ Auth::user()->company->reportsTo()->email }}</a>
                        @endif
                    </address>
                @else
                    <address class="margin-bottom-40">
                        Phone: (02) 9849 4444<br>
                        Email: <a href="mailto:company@capecod.com.au">company@capecod.com.au</a>
                    </address>
                @endif
            </div>

            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                @if (in_array(Auth::user()->company_id, [41,198, 210, 302]) || in_array(Auth::user()->parent_company, [41, 198, 210, 302]))
                    @if (in_array(Auth::user()->company_id, [41,198, 210, 302]))
                        <h2>{{ Auth::user()->company->name }}</h2>
                        <p>ABN {{ Auth::user()->company->abn }}<br></p>
                    @else
                        <h2>{{ Auth::user()->company->reportsTo()->name }}</h2>
                        <p>ABN {{ Auth::user()->company->reportsTo()->abn }}<br></p>
                    @endif
                @else
                    <h2>Cape Cod Australia Pty Ltd</h2>
                    <p>ABN 54 000 605 407<br>
                        Builders Lic. No 5519</p>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- END PRE-FOOTER -->
<!-- BEGIN INNER FOOTER -->
<div class="page-footer">
    <div class="container">
        Licensed to
        @if (in_array(Auth::user()->company_id, [41,198, 210, 302]) || in_array(Auth::user()->parent_company, [41, 198, 210, 302]))
            @if (in_array(Auth::user()->company_id, [41,198, 210]))
                {{ Auth::user()->company->name }}
            @else
                {{ Auth::user()->company->reportsTo()->name }}
            @endif
        @else
            Cape Cod
        @endif
    </div>
</div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>
<!-- END INNER FOOTER -->