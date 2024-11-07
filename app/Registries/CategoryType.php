<?php

namespace App\Registries;

enum CategoryType: string
{
    case Articles             = 'ARTICLES';
    case Skins                = 'SKINS';
    case BedrockResourcePacks = 'BEDROCK_RESOURCE_PACKS';
    case BedrockAddons        = 'BEDROCK_ADDONS';
    case BedrockMaps          = 'BEDROCK_MAPS';
    case JavaResourcePacks    = 'JAVA_RESOURCE_PACKS';
    case JavaDataPacks        = 'JAVA_DATA_PACKS';
    case JavaMods             = 'JAVA_MODS';
    case JavaMaps             = 'JAVA_MAPS';
}
