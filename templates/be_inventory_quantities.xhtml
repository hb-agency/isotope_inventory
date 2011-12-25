<form action="<?php echo $this->Environment->request ?>" method="post">
  <div class="tl_formbody">
    <div class="tl_panel">
      <div class="tl_submit_panel tl_subpanel">
      <input type="hidden" name="FORM_SUBMIT" value="tl_filters">
        <input type="image" name="filter" id="filter" src="system/themes/default/images/reload.gif" class="tl_img_submit" title="<?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'apply' ] ?>" value="<?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'apply' ] ?>">
      </div>

      <div class="tl_limit tl_subpanel">
        <strong><?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'showOnly' ] ?>:</strong>
        <select name="tl_limit" class="tl_select" onchange="this.form.submit()">
          <option value="tl_limit"><?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'filterAll' ] ?></option>
          <?php foreach ( $this->limits as $limit => $text ) : ?>
          <option value="<?php echo $limit ?>"<?php if ( $this->limit == $limit ) echo ' selected="selected"' ?>><?php echo $text ?></option>
          <?php endforeach ?>
        </select> 
      </div>

      <div class="tl_search tl_subpanel">
      <label for="override"><strong>Override Quantities Mode</strong></label><input type="checkbox" name="override" id="ctrl_override" value="1" />
      <strong><?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'search' ] ?>:</strong>
      <select name="tl_field" class="tl_select">
        <?php foreach ( $this->fields as $field ) : ?>
        <option value="<?php echo $field ?>"<?php if ( $this->tl_field == $field ) echo ' selected="selected"' ?>><?php echo $GLOBALS[ 'TL_LANG' ][ 'tl_iso_products' ][ $field ][0] ?></option>
        <?php endforeach ?>
      </select>
      <span>=</span>
      <input type="text" name="tl_value" class="tl_text" value="<?php echo $this->tl_value ?>">
    </div>

    <div class="clear"></div>
</div>
</form>


<div id="tl_buttons">
  <a href="<?php echo $this->href ?>" class="header_back" title="<?php echo $this->title ?>"><?php echo $this->title ?></a>
</div>

<h2 class="sub_headline"><?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'manage_quantities' ]; ?></h2>

<form action="<?php echo $this->Environment->request ?>" method="post">
  <input type="hidden" name="FORM_SUBMIT" value="tl_quantities">
  <div class="items tl_formbody_edit">
      <?php if($this->products): ?>
      <?php foreach ( $this->products as $product ) : ?>
      <div class="item<?php echo ($product->has_variants ? ' parent' : ''); ?>">
        <div class="name"><a href="contao/main.php?do=iso_products&act=edit&id=<?php echo $product->id; ?>"><?php echo $product->name; ?></a></div>
        <div class="sku"><?php echo $product->sku; ?></div>
        <div class="current"><?php if($product->has_variants): ?>Total <?php else: ?> Currently<?php endif; ?> : <span class="current_quantity"> <?php if($product->has_variants): echo $product->total_quantity; else: echo $product->quantity; endif; ?></span></div>
        <div class="new_value"><?php if(!$product->has_variants): ?><input type="text" class="tl_text" name="quantities[<?php echo $product->id; ?>]" value="" /><?php else: ?>&nbsp;<?php endif; ?></div>
      </div><!-- .item -->
      <?php endforeach ?>
      <?php else: ?>
      <div class="error"><?php echo $this->noProducts; ?></div>
      <?php endif; ?>
  </div><!-- .items -->

  <div class="tl_formbody_submit">
    <div class="tl_submit_container">
      <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="<?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'save' ] ?>">
      <input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="<?php echo $GLOBALS[ 'TL_LANG' ][ 'MSC' ][ 'saveNclose' ] ?>">
    </div>
  </div>
</form>
