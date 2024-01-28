<?php
namespace AugmentedSteam\Server\Model\Market;

enum EType: string
{
    case Unknown = "unknown";
    case Background = "background";
    case Booster = "booster";
    case Card = "card";
    case Emoticon = "emoticon";
    case Item = "item";
}
