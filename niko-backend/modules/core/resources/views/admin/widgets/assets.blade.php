@php
    /**
     * @var $position string
     * @var $assetManager \WezomCms\Core\Contracts\Assets\AssetManagerInterface
     */
@endphp
@foreach($assetManager->getCss($position) as $style)
    {{{ $style }}}
@endforeach

@foreach($assetManager->getJs($position) as $script)
    {{{ $script }}}
@endforeach

