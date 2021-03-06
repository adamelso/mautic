<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index')
$view->extend('MauticAssetBundle:Asset:index.html.php');
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered asset-list" id="assetTable">
            <thead>
            <tr>
                <th class="visible-md visible-lg col-asset-actions pl-20">
                    <div class="checkbox-inline custom-primary">
                        <label class="mb-0 pl-10">
                            <input type="checkbox" id="customcheckbox-one0" value="1" data-toggle="checkall" data-target="#assetTable">
                            <span></span>
                        </label>
                    </div>
                </th>
                <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'asset',
                    'orderBy'    => 'a.title',
                    'text'       => 'mautic.core.title',
                    'class'      => 'col-asset-title',
                    'default'    => true
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'asset',
                    'orderBy'    => 'c.title',
                    'text'       => 'mautic.core.category',
                    'class'      => 'visible-md visible-lg col-asset-category'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'asset',
                    'orderBy'    => 'a.downloadCount',
                    'text'       => 'mautic.asset.asset.thead.download.count',
                    'class'      => 'visible-md visible-lg col-asset-download-count'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'asset',
                    'orderBy'    => 'a.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'visible-md visible-lg col-asset-id'
                ));
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $k => $item): ?>
                <tr>
                    <td class="visible-md visible-lg">
                        <?php
                        echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', array(
                            'item'       => $item,
                            'templateButtons' => array(
                                'edit'       => $security->hasEntityAccess($permissions['asset:assets:editown'], $permissions['asset:assets:editother'], $item->getCreatedBy()),
                                'delete'     => $security->hasEntityAccess($permissions['asset:assets:deleteown'], $permissions['asset:assets:deleteother'], $item->getCreatedBy()),
                            ),
                            'routeBase'  => 'asset',
                            'langVar'    => 'asset.asset',
                            'nameGetter' => 'getTitle',
                            'customButtons' => array(
                                array(
                                    'attr' => array(
                                        'data-toggle' => 'ajaxmodal',
                                        'data-target' => '#AssetPreviewModal',
                                        'href' => $view['router']->generate('mautic_asset_action', array('objectAction' => 'preview', 'objectId' => $item->getId()))
                                    ),
                                    'btnText'   => $view['translator']->trans('mautic.asset.asset.preview'),
                                    'iconClass' => 'fa fa-image'
                                )
                            )
                        ));
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php',array(
                                'item'       => $item,
                                'model'      => 'asset.asset'
                            )); ?>
                            <a href="<?php echo $view['router']->generate('mautic_asset_action',
                                array("objectAction" => "view", "objectId" => $item->getId())); ?>"
                               data-toggle="ajax">
                                <?php echo $item->getTitle(); ?> (<?php echo $item->getAlias(); ?>)
                            </a>
                            <i class="<?php echo $item->getIconClass(); ?>"></i>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4"><small><?php echo $description; ?></small></div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#' . $category->getColor() : 'inherit'; ?>
                        <span class="label label-default pa-5" style="background: <?php echo $color; ?>;"> </span>
                        <span><?php echo $catName; ?></span>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getDownloadCount(); ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel-footer">
        <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', array(
            "totalItems"      => count($items),
            "page"            => $page,
            "limit"           => $limit,
            "menuLinkId"      => 'mautic_asset_index',
            "baseUrl"         => $view['router']->generate('mautic_asset_index'),
            'sessionVar'      => 'asset'
        )); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', array('tip' => 'mautic.asset.noresults.tip')); ?>
<?php endif; ?>

<?php echo $view->render('MauticCoreBundle:Helper:modal.html.php', array(
    'id'     => 'AssetPreviewModal',
    'header' => false
));
