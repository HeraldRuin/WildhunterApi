@php
$main_color = setting_item('style_main_color', '#5291fa');
$email_header = setting_item_with_lang('email_header');
if ($email_header) {
    $email_header = preg_replace_callback(
        '/<a\b([^>]*)>/i',
        function ($matches) {
            $attrs = $matches[1];
            $linkStyle = 'color:#ffffff !important;text-decoration:none !important;';
            if (preg_match('/style\s*=\s*([\'"])(.*?)\1/i', $attrs, $styleMatch)) {
                $style = rtrim($styleMatch[2], ';') . ';' . $linkStyle;
                $attrs = preg_replace('/style\s*=\s*([\'"]).*?\1/i', 'style="' . $style . '"', $attrs, 1);
            } else {
                $attrs .= ' style="' . $linkStyle . '"';
            }
            return '<a' . $attrs . '>';
        },
        $email_header
    );
}
@endphp
<div class="" style="">
    <div class="b-container">
        <div class="b-header" bgcolor="{{ $main_color }}" x-apple-data-detectors="false" style="background-color:{{ $main_color }};color:#ffffff;padding:30px;">
            {!! $email_header ? $email_header : sprintf('<h1 class="site-title" style="margin:0;display:block;text-align:center;color:#ffffff !important;">%s</h1>', setting_item('site_title','Wild Hunter')) !!}
        </div>
    </div>
</div>
