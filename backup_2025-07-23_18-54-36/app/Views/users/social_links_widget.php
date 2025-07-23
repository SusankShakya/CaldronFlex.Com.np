<?php

//available social link types and icon 
$social_link_icons = array(
    "facebook" => "facebook",
    "twitter" => "twitter",
    "linkedin" => "linkedin",
    "whatsapp" => "phone",
    "youtube" => "youtube",
    "instagram" => "instagram",
    "github" => "github",
);

$social_link_svg_icons = array(
    "digg" => "digg",
    "pinterest" => "pinterest",
    "tumblr" => "tumblr",
    "vine" => "vine",
);

$links = "";

foreach ($social_link_icons as $key => $icon) {
    if (isset($weblinks->$key) && $weblinks->$key) {
        $address = to_url($weblinks->$key); //check http or https in url
        $links .= "<a target='_blank' href='$address' class='social-link'><i data-feather='$icon' class='icon-16'></i></a>";
    }
}

foreach ($social_link_svg_icons as $key => $icon) {
    if (isset($weblinks->$key) && $weblinks->$key) {
        $address = to_url($weblinks->$key); //check http or https in url
        $links .= "<a target='_blank' href='$address' class='social-link custom-svg'>" . view("users/svg_social_icons/$icon") . "</a>";
    }
}
echo $links;
?>
