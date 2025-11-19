<?php

declare(strict_types=1);

namespace Wezom\Core\Media;

enum ConversionOperationEnum: string
{
    case GAMMA = 'gamma';
    case CONTRAST = 'contrast';
    case BLUR = 'blur';
    case COLORIZE = 'colorize';
    case GREYSCALE = 'greyscale';
    case SEPIA = 'sepia';
    case SHARPEN = 'sharpen';
    case FIT = 'fit';
    case PICK_COLOR = 'pickColor';
    case RESIZE_CANVAS = 'resizeCanvas';
    case MANUAL_CROP = 'manualCrop';
    case CROP = 'crop';
    case FOCAL_CROP = 'focalCrop';
    case BACKGROUND = 'background';
    case OVERLAY = 'overlay';
    case ORIENTATION = 'orientation';
    case FLIP = 'flip';
    case PIXELATE = 'pixelate';
    case WATERMARK = 'watermark';
    case INSERT = 'insert';
    case RESIZE = 'resize';
    case WIDTH = 'width';
    case HEIGHT = 'height';
    case BORDER = 'border';
    case QUALITY = 'quality';
    case FORMAT = 'format';
    case OPTIMIZE = 'optimize';
    case NON_OPTIMIZED = 'nonOptimized';
}
