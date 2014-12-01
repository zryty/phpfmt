<?php
class TightConcat extends AdditionalPass {
	public function format($source) {
		$whitespaces = " \t";
		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->get_token($token);
			$this->ptr = $index;
			switch ($id) {
				case ST_CONCAT:
					if (!$this->left_token_is([T_LNUMBER, T_DNUMBER])) {
						$this->code = rtrim($this->code, $whitespaces);
					}
					if (!$this->right_token_is([T_LNUMBER, T_DNUMBER])) {
						each($this->tkns);
					}
				default:
					$this->append_code($text);
					break;
			}
		}
		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function get_description() {
		return 'Ensure string concatenation does not have spaces, except when close to numbers.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function get_example() {
		return <<<'EOT'
<?php
// From
$a = 'a' . 'b';
$a = 'a' . 1 . 'b';
// To
$a = 'a'.'b';
$a = 'a'. 1 .'b';
?>
EOT;
	}
}