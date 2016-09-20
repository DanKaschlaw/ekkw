<?php

class WPFTS_Htmltools {
	
	static function makeNode($name, $attrs = array(), $html = false) {
		$s = '<'.$name.' ';
		foreach ($attrs as $k => $d) {
			$s .= ' '.htmlspecialchars($k).'="'.htmlspecialchars($d).'"';
		}
		
		if ($html !== false) {
			$s .= '>'.$html.'</'.$name.'>';
		} else {
			$s .= '/>';
		}
		
		return $s;
	}
	
	static function makeSelect($data, $current = false, $attrs = array()) {
		
		$html = '';
		foreach ($data as $k => $v) {
			$html .= '<option value="'.htmlspecialchars($k).'" '.((($current !== false) && ($current == $k)) ? ' selected="selected"' : '').'>'.htmlspecialchars($v).'</option>';
		}
		
		return self::makeNode('select', $attrs, $html);
	}
	
	static function makeMultiSelect($data, $current = array(), $attrs = array()) {
		
		$attrs['multiple'] = 'multiple';
		
		$html = '';
		foreach ($data as $k => $v) {
			$html .= '<option value="'.htmlspecialchars($k).'" '.((($current !== false) && (in_array($k, $current))) ? ' selected="selected"' : '').'>'.htmlspecialchars($v).'</option>';
		}
		
		return self::makeNode('select', $attrs, $html);
	}
	
	static function makeRadioGroup($basename, $data, $current = false, $attrs = array()) {
		
		$html = '';
		
		if (is_array($data)) {
			$uniq = '';
			foreach ($data as $k => $d) {
				$id = 'rg'.$uniq.$basename.'_'.$k;
				$html .= '<label for="'.htmlspecialchars($id).'"><input type="radio" name="'.htmlspecialchars($basename).'" id="'.htmlspecialchars($id).'" value="'.htmlspecialchars($k).'"'.($current == $k ? ' checked="checked"' : '').'>&nbsp;'.htmlspecialchars($d).'</label>';
			}
		}
		
		return $html;
	}
	
	static function makeText($current = false, $attrs = array()) {
		
		$html = '';
		$attrs['type'] = 'text';
		if ($current !== false) {
			$attrs['value'] = $current;
		}
		return self::makeNode('input', $attrs, $html);
	}
	
	static function makeTextarea($current = '', $attrs = array()) {
		
		$html = htmlspecialchars($current);
		return self::makeNode('textarea', $attrs, $html);
	}
	
	static function makeButton($caption = '', $attrs = array()) {
		
		$html = $caption;
		return self::makeNode('button', $attrs, $html);
	}
	
	static function makeCheckbox($current = 0, $attrs = array(), $label = false) {
		if ($current) {
			$attrs['checked'] = 'checked';
		} else {
			if (isset($attrs['checked'])) {
				unset($attrs['checked']);
			}
		}
		$attrs['type'] = 'checkbox';
		
		$html = self::makeNode('input', $attrs).$label;
		if ($label !== false) {
			$l_attrs = array();
			if (isset($attrs['id'])) {
				$l_attrs['for'] = $attrs['id'];
			}
			return self::makeNode('label', $l_attrs, $html);
		} else {
			return $html;
		}
	}
	
	static function makeLabelledCheckbox($name, $value, $label, $ischecked = false) {
		
		$id = 'lch'.$name;
		
		$attrs = array(
			'type' => 'checkbox',
			'value' => $value,
			'name' => $name,
			'id' => $id,
		);
		if ($ischecked) {
			$attrs['checked'] = 'checked';
		}
	
		$html = '<label for="'.$id.'">'.self::makeNode('input', $attrs).'&nbsp;<span>'.$label.'</span></label>';
		
		return $html;
	}
}