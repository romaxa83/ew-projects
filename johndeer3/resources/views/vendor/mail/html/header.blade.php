<tr>
    <td class="header">
        <div style="display: flex">
            <div style="width:50%;float:left">
                <img style="height: 50px;float: right" src="{{asset('storage/logo.png')}}" alt="jd" title="jd">
{{--                <img style="height: 50px;float: right" src="{{asset('storage/logo.svg')}}" alt="jd" title="jd">--}}
{{--                <img style="height: 50px;float: right" src="{{url('/storage/logo.png',[],true)}}" alt="jd" title="jd">--}}
{{--                <img style="height: 50px;float: right" src="{{url('/static/logo.png',[],true)}}" alt="jd" title="jd">--}}
{{--                <img style="height: 50px;float: right" src="{{url('/static/logo.png',[],true)}}" alt="jd" title="jd">--}}
            </div>
            <div style="width:50%;margin-left: 3%">
                <a style="float: left;height: 50px;line-height: 50px;font-size: 22px" href="{{ $url }}">
                    {{ prettyAppName() }}
                </a>
            </div>
        </div>
    </td>
</tr>
