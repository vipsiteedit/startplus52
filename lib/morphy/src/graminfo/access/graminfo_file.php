<?php
 /**
 * This file is part of phpMorphy library
 *
 * Copyright c 2007-2008 Kamaev Vladimir <heromantor@users.sourceforge.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, Inc., 59 Temple Place - Suite 330,
 * Boston, MA 02111-1307, USA.
 */

/**
 * This file is autogenerated at Fri, 16 May 2008 01:44:27 +0400, don`t change it!
 */
class phpMorphy_Graminfo_File extends phpMorphy_Graminfo {
	function readGramInfoHeader($offset) {
		$fh = $this->resource;
		fseek($fh, $offset); 
		
		$result = unpack(
			'vid/vfreq/vancodes_offset/vfull_size/vbase_size',
			fread($fh, 4) 
		);
		
		$result['offset'] = $offset;
		$result['all_size'] = $result['ancodes_offset'];
		$result['ancodes_size'] = $result['full_size'] - $result['all_size'];
		
		return $result;
	}

	function readAncodes($info) {
		$fh = $this->resource;
		fseek($fh, $info['offset'] + 10 + $info['all_size']); 
		return explode("\x0", fread($fh, $info['ancodes_size'] - 1));
	}
	
	function readFlexiaData($info, $onlyBase) {
		$fh = $this->resource;
		fseek($fh, $info['offset'] + 10); 
		return explode("\x0", fread($fh, ($onlyBase ? $info['base_size'] : $info['all_size']) - 1));
	}
	
	function readAllGramInfoOffsets() {
		$fh = $this->resource;
		
		$result = array();
		for($offset = 0x100, $i = 0, $c = $this->header['flex_count']; $i < $c; $i++) {
			$result[] = $offset;
			
			$header = $this->readGramInfoHeader($offset);
			
			// skip padding
			$flexia_size = 10 + $header['full_size'];
			
			fseek($fh, $offset + $flexia_size); 
			$pad_len = ord(fread($fh, 1));
			
			$offset += $flexia_size + $pad_len + 1;
		}
		
		return $result;
	}
}
