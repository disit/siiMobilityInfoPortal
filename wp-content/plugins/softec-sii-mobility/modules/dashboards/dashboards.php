<?php

// Interrompe l'esecuzione se il file è richiamato direttamente
if ( ! function_exists( 'add_action' ) ) {
    exit;
}

include( 'includes/softec-siimob-post-type.php' );

add_action('init', 'siimob_dashboards_init');

function siimob_dashboards_init()
{
    register_dashboard_post_type();
}


add_shortcode('custom_dashboard_page','build_custom_dashboard_page');

function build_custom_dashboard_page()
{
    $oUser = wp_get_current_user();
    $aDashboards = calculate_user_dashboards($oUser->ID);

    ?>
    <div class="row mosaic">
        <?php 
        foreach ($aDashboards as $iDashboard) : 
            $sUrl = get_post_meta($iDashboard, SIIMOB_DASHBOARD_URL_META_FIELD_NAME, true);
            $sColumn = get_post_meta($iDashboard, SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME, true);
            $iHeight = get_post_meta($iDashboard, SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME, true);
            $sHeight = empty($iHeight) ? '500px' : $iHeight . 'px';

        ?>
            <div class="item col-md-<?php echo $sColumn; ?>">
                <iframe style="width: 100%; height: <?php echo $sHeight; ?>;" src="<?php echo $sUrl;?>"></iframe>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function calculate_user_dashboards($iUserId)
{
    $aDashboards = array();
    if (! empty($iUserId)) {
        $aDashboards = get_user_meta($iUserId, SIIMOB_USER_DASHBOARD_META_FIELD_NAME, true);
        $iProvince = get_user_meta($iUserId, SIIMOB_USER_PROVINCE_META_FIELD_NAME, true);
        $iType = get_user_meta($iUserId, SIIMOB_USER_TYPE_META_FIELD_NAME, true);

        if (empty($aDashboards) && (! empty($iProvince) || ! empty($iType))) {

            $aArgs = array(
                'meta_query' => array(
                    'key'     => SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME,
                    'value'   => 'yes',
                    'compare' => '='
                ),
                'post_type' => SIIMOB_DASHBOARD_POST_TYPE_NAME,
                'numberposts' => 1,
                'fields' => 'ids'
            );

            $aTaxQuery = array('relation' => 'AND');

            if (! empty($iProvince)) {
                $aTaxQuery[] = array(
                    'taxonomy'  => SIIMOB_PROVINCE_TAXONOMY_NAME,
                    'field'     => 'term_id',
                    'terms'     => $iProvince
                );
            }
            if (! empty($iType)) {
                $aTaxQuery[] = array(
                    'taxonomy'  => SIIMOB_TYPE_TAXONOMY_NAME,
                    'field'     => 'term_id',
                    'terms'     => $iType
                );
            }
            $aArgs['tax_query'] = $aTaxQuery;

            $aDashboards = get_posts($aArgs);
        }
    }

    if (empty($aDashboards) || empty($iUserId)) {
        $aArgs = array(
            'meta_query' => array(
                array(
                    'key'     => SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME,
                    'value'   => 'yes',
                    'compare' => '='
                )
            ),
            'post_type' => SIIMOB_DASHBOARD_POST_TYPE_NAME,
            'numberposts' => 1,
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'ASC'
        );
        $aDashboards = get_posts($aArgs);
    }

    return $aDashboards;
}

add_shortcode('custom_dashboard_generic_page','build_custom_dashboard_generic_page');

function build_custom_dashboard_generic_page()
{
    $aArgs = array(
        'meta_query' => array(
            array(
                'key'     => SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME,
                'value'   => 'yes',
                'compare' => '='
            )
        ),
        'post_type' => SIIMOB_DASHBOARD_POST_TYPE_NAME,
        'numberposts' => 1,
        'fields' => 'ids',
        'orderby' => 'date',
        'order' => 'ASC'
    );
    $oUser = wp_get_current_user();
    $iUserId = $oUser->ID;
    
    if (! empty($iUserId)) {
        $iProvince = get_user_meta($iUserId, SIIMOB_USER_PROVINCE_META_FIELD_NAME, true);
        $iType = get_user_meta($iUserId, SIIMOB_USER_TYPE_META_FIELD_NAME, true);
        $aTaxQuery = array();
        if (! empty($iProvince) || ! empty($iType)) {
            $aTaxQuery = array('relation' => 'AND');
            if (! empty($iProvince)) {
                $aTaxQuery[] = array(
                    'taxonomy'  => SIIMOB_PROVINCE_TAXONOMY_NAME,
                    'field'     => 'term_id',
                    'terms'     => $iProvince
                );
            }
            if (! empty($iType)) {
                $aTaxQuery[] = array(
                    'taxonomy'  => SIIMOB_TYPE_TAXONOMY_NAME,
                    'field'     => 'term_id',
                    'terms'     => $iType
                );
            }
        }
        if(! empty($aTaxQuery)) {
            $aArgs['tax_query'] = $aTaxQuery;
        }
    }
    $aDashboards = get_posts($aArgs);
    $sUrl = get_post_meta($aDashboards[0], SIIMOB_DASHBOARD_URL_META_FIELD_NAME, true);
    $iHeight = get_post_meta($aDashboards[0], SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME, true);
    $sHeight = empty($iHeight) ? '500px' : $iHeight . 'px';

    ?>
        <div class="row">
            <div class="item col-md-12">
                <iframe style="width: 100%; height: <?php echo $sHeight; ?>;" src="<?php echo $sUrl;?>" onload="resizeIframe(this)"></iframe>
            </div>
        </div>
    <?php
}