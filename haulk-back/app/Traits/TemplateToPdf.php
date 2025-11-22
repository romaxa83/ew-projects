<?php

namespace App\Traits;

use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;

trait TemplateToPdf
{
    /**
     * @param string|array $templateName
     * @param $data
     * @param bool $stream
     * @param string $filename
     * @return string|null
     * @throws Throwable
     */
    public function template2pdf(
        $templateName,
        $data,
        bool $stream = true,
        string $filename = 'document.pdf'
    ): ?string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ]);
        $dompdf->setHttpContext($context);

        $templateRendered = '';
        if(is_array($templateName)){
            if(count($templateName) == 1){
                $templateRendered = view(current($templateName), $data)->render();
            } else {
                foreach ($templateName as $name) {
                    $templateRendered .= '<pagebreak>' . view($name, $data)->render();
                }

                $templateRendered = $this->str_replace_first('<pagebreak>', '', $templateRendered);
            }
        } else {
            $templateRendered = view($templateName, $data)->render();
        }

        $dompdf->loadHtml($templateRendered);

        $dompdf->setPaper('A4');
        $dompdf->render();

        if ($stream) {
            $dompdf->stream($filename, [
                'compress' => 1,
                'Attachment' => 0,
            ]);
            return null;
        }

        return $dompdf->output();
    }

    function str_replace_first($search, $replace, $subject) {
        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}
