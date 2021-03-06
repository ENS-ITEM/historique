<?php
$pageTitle = __('Curation History Log (%d total)', $total_results);
echo head(array(
    'title' => $pageTitle,
    'bodyclass' => 'history-log browse',
));
?>
<style>
div.record-title {
    max-width: 15em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
<div id="primary">
    <?php echo flash(); ?>
    <div>
<?php if (total_records('HistoryLogEntry') > 0): ?>
        <div class="table-actions">
            <a href="<?php echo html_escape(url('history-log/index/search')); ?>" class="add button small green"><?php echo __('Advanced Reports'); ?></a>
        </div>
        <?php echo common('quick-filters'); ?>
        <div id="item-filters">
            <ul>
                <?php if (!empty($params['record_type'])): ?>
                <li><?php echo __('Record Type: %s', $params['record_type']); ?></li>
                <?php endif; ?>
                <?php if (!empty($params['part_of'])): ?>
                <li><?php echo __('Part of #%s', $params['part_of']); ?></li>
                <?php endif; ?>
                <?php if (!empty($params['since'])): ?>
                <li><?php echo __('Since %s', $params['since']); ?></li>
                <?php endif; ?>
                <?php if (!empty($params['until'])): ?>
                <li><?php echo __('Until %s', $params['until']); ?></li>
                <?php endif; ?>
                <?php if (!empty($params['user'])): ?>
                <li><?php
                    $user = get_record_by_id('User', $params['user']);
                    echo $user
                        ? __('User: %s', $user->username)
                        : __('User: #%s', $params['user']);
                ?></li>
                <?php endif; ?>
                <?php if (!empty($params['operation'])): ?>
                <li><?php echo __('Operation: %s', ucfirst($params['operation'])); ?></li>
                <?php endif; ?>
                <?php if (!empty($params['element'])): ?>
                <li><?php
                    $element = get_record_by_id('Element', $params['element']);
                    echo $element
                        ? __('Element %s (%s)', $element->name, $element->set_name)
                        : __('Element #%s', $params['element']);
                ?></li>
                <?php endif; ?>
            </ul>
    </div>
        <?php if (iterator_count(loop('HistoryLogEntry'))): ?>
        <div class="pagination"><?php echo $paginationLinks = pagination_links(); ?></div>
        <table id="history-log-entries">
            <thead>
                <tr>
                    <?php
                    $browseHeadings[__('Date')] = 'date';
                    $browseHeadings[__('Contenu')] = 'record_type';
                    $browseHeadings[__('Id')] = 'record_id';
//                     $browseHeadings[__('Part of ')] = 'part_of';
                    $browseHeadings[__('User')] = 'user';
                    $browseHeadings[__('Action')] = 'operation';
                    $browseHeadings[__('Changes')] = null;
                    echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php $key = 0; ?>
                <?php
                foreach (loop('HistoryLogEntry') as $logEntry):
                ?>
                <tr class="history-log-entry <?php echo ++$key%2 == 1 ? 'odd' : 'even'; ?>">
                    <td><?php echo $logEntry->added; ?></td>
                    <td colspan="2">
                        <?php 
                          $link = '';
                        if ($logEntry->record_type == 'Simple Page' || $logEntry->record_type == 'Exhibit Page' || $logEntry->record_type == 'Transcription') {
//                           if ($params['operation'] <> 'delete') {
                            if ($logEntry->record_type == 'Simple Page') {
                              $ss = get_record_by_id('SimplePagesPage', $logEntry->record_id);
                              if ($ss) {
                                $link = WEB_ROOT . "/" . $ss->slug;                              
                              } else {
                                echo "Simple Page effac&eacute;e.";                                 
                              }
                            } elseif ($logEntry->record_type == 'Transcription') {
                              $link = WEB_ROOT . "/transcription/" . $logEntry->record_id;
                            } elseif ($logEntry->record_type == 'Exhibit Page') {
                              $db = get_db();
                              $ex = $db->query('SELECT slug, exhibit_id eid FROM omeka_exhibit_pages WHERE id = ' . $logEntry->record_id)->fetchAll();
                              if (!$ex) {
                                echo "Page d'exposition effac&eacute;e.";                                 
                              } else {
                                $query = 'SELECT slug FROM omeka_exhibits WHERE id = ' . (int)$ex[0]['eid'];
                                $parentExhibit = $db->query($query)->fetchAll();                           
                                $link = WEB_ROOT . "/exhibits/show/" . $parentExhibit[0]['slug'] . '/' . $ex[0]['slug'];                                
                              }
//                             }                            
                          }                            
                          if ($link) {
                            echo "<a href='$link'>" . $logEntry->record_type . "</a>";                                                      
                          }
                          echo ' ';
                          echo $logEntry->record_id;                          
                        } else {
                        ?>
                        <a href="<?php
                        echo url(array(
                                'type' => Inflector::tableize($logEntry->record_type),
                                'id' => $logEntry->record_id,
                            ), 'history_log_record_log'); ?>"><?php
                            echo $logEntry->record_type;
                            echo ' ';
                            echo $logEntry->record_id;
                        ?></a>    
                        <div class="record-title"><?php echo $logEntry->displayCurrentTitle(); ?></div>                                              
                        <?php } ?>
                    </td>
                    <td><?php echo $logEntry->displayUser(); ?></td>
                    <td><?php echo $logEntry->displayOperation(); ?>
                    <?php if ($logEntry->isEntryToUndelete()):
                        $undeleteUrl = url(array(
                                'type' => Inflector::tableize($logEntry->record_type),
                                'id' => $logEntry->record_id,
                            ), 'history_log_undelete'); ?>
                        <div><a href="<?php echo html_escape($undeleteUrl); ?>" class="history-log-process button red"><?php echo html_escape(__('Undo')); ?></a></div>
                    <?php endif;
                    ?></td>
                    <td><?php echo nl2br($logEntry->displayChanges(), true); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination"><?php echo $paginationLinks; ?></div>
        <?php else: ?>
        <br class="clear" />
        <div>
            <p><?php echo __('The query searched %s records and returned no results.', total_records('HistoryLogEntry')); ?></p>
            <p><?php echo __('Display all %shistory log entries%s.', '<a href="' . url('history-log') . '">', "</a>"); ?></p>
        </div>
        <?php endif; ?>
<?php else: ?>
        <p><?php echo __('No entry have been logged.'); ?></p>
<?php endif; ?>
    </div>
</div>
<?php
echo foot();
