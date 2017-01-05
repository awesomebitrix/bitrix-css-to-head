<?
// Обработка буферизации
$eventManager->addEventHandler('main', 'OnEndBufferContent', 'ChangeMyContent');
function ChangeMyContent(&$content)
{
    if (strpos($_SERVER['REQUEST_URI'], '/bitrix/admin/') !== false)
        return false;

    $arCss = array();
    $dom = new DOMDocument;
    $dom->loadHTML(Bitrix\Main\Page\Asset::getInstance()->getCss());

    foreach ($dom->getElementsByTagName('link') as $node) {
        $href = preg_replace('/\?(.*)/', '', $node->getAttribute( 'href' ));

        if (strpos($href, 'template_') === false)
            $content = preg_replace('/<link\shref="'. addcslashes($href, '/') .'(.*)>/', '', $content);
        else
            $arCss[] = $href;
    }

    foreach($arCss as $val) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $val;
        if (file_exists($full_path))
        {
            $style = trim( file_get_contents($full_path) );
            if (!empty($style)) {
                $search  = '<style type="text/css"></style>';
                $replace = '<style type="text/css">'. $style .'</style>';

                $content = str_replace($search, $replace, $content);
                $content = preg_replace('/<link\shref="'.  addcslashes($val, '/') .'(.*)>/', '', $content);
            }
        }
    }

    $arReplace = array(
        "/\\/\\*(.*)\\*\\//"    => "",
        "/\n+/"                 => "\n",
    );
    $content = preg_replace(array_flip($arReplace), $arReplace, $content);
}