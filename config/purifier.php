<?php

return [
    'material' => [
        'HTML.Doctype' => 'HTML 4.01 Transitional',
        'HTML.AllowedElements' => 'h2,h3,h4,p,strong,em,ul,ol,li,a,img,blockquote,u',
        'HTML.AllowedAttributes' => 'href,target,src,alt,class',
        'Attr.AllowedFrameTargets' => ['_blank'],
        'URI.AllowedSchemes' => ['http' => true, 'https' => true],
    ],

    'comment' => [
        'HTML.Doctype' => 'HTML 4.01 Transitional',
        'HTML.AllowedElements' => 'p,strong,em,ul,ol,li,blockquote,u,a',
        'HTML.AllowedAttributes' => 'href,target',
        'Attr.AllowedFrameTargets' => ['_blank'],
        'URI.AllowedSchemes' => ['http' => true, 'https' => true],
    ],
];
