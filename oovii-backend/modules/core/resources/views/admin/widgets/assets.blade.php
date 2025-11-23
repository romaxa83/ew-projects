@php
    /**
     * @var $position string
     * @var $assetManager \WezomCms\Core\Contracts\Assets\AssetManagerInterface
     */
@endphp
@foreach($assetManager->getCss(\WezomCms\Core\Contracts\Assets\AssetManagerInterface::GROUP_ADMIN, $position) as $style)
    {{{ $style }}}
@endforeach

@foreach($assetManager->getJs(\WezomCms\Core\Contracts\Assets\AssetManagerInterface::GROUP_ADMIN, $position) as $script)
    {{{ $script }}}
@endforeach
