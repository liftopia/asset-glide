<?php
namespace Liftopia\Manipulator;

use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;
use League\Glide\Api\Manipulator\Size;
use Symfony\Component\HttpFoundation\Request;

class FillCanvasSize extends Size
{
    /**
     * Perform size image manipulation.
     *
     * @param  Request $request The request object.
     * @param  Image   $image   The source image.
     * @return Image   The manipulated image.
     */
    public function run(Request $request, Image $image)
    {
        $width = $this->getWidth($request->get('w'));
        $height = $this->getHeight($request->get('h'));
        $fit = $this->getFit($request->get('fit'));
        $crop = $this->getCrop($request->get('crop'));
        $bgColor = $this->getBgColor($request->get('bg'));

        list($width, $height) = $this->resolveMissingDimensions($image, $width, $height);
        list($width, $height) = $this->limitImageSize($width, $height);

        if (round($width) !== round($image->width()) ||
            round($height) !== round($image->height())) {
            $image = $this->runResize($image, $fit, round($width), round($height), $crop, $bgColor);
        }

        return $image;
    }

    /**
     * Resolve background color.
     *
     * @param  string $bgColor The background color
     * @return string The resolved background color
     */
    public function getBgColor($bgColor)
    {
        if (empty($bgColor)) {
            return null;
        }

        $bgColor = '#'.ltrim($bgColor, '#');
        $hexColorValidationRegex = '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/';

        if (!preg_match($hexColorValidationRegex, $bgColor)) {
            return null;
        }

        return $bgColor;
    }

    /**
     * Resolve fit.
     *
     * @param  string $fit The fit.
     * @return string The resolved fit.
     */
    public function getFit($fit)
    {
        if ($fit === 'fill') {
            return 'fill';
        }

        return parent::getFit($fit);
    }

    /**
     * Perform resize image manipulation.
     *
     * @param Image $image The source image.
     * @param string $fit The fit.
     * @param string $width The width.
     * @param string $height The height.
     * @param string|null $crop The crop.
     * @param string $bgColor The background color
     * @return Image The manipulated image.
     */
    public function runResize(Image $image, $fit, $width, $height, $crop = null, $bgColor = '#ffffff')
    {
        if ($fit === 'fill') {
            return $this->runFillResize($image, $width, $height, $bgColor);
        }

        return parent::runResize($image, $fit, $width, $height, $crop);
    }

    /**
     * Perform resize with fill image manipulation.
     *
     * @param Image $image The source image.
     * @param string $width The width.
     * @param string $height The height.
     * @param string $bgColor The background color
     * @return Image The manipulated image.
     */
    public function runFillResize(Image $image, $width, $height, $bgColor)
    {
        $resized = $this->runMaxResize($image, $width, $height);
        return $resized->resizeCanvas($width, $height, 'center', false, $bgColor);
    }
}
