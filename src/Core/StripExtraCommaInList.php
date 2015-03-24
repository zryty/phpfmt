<?php
final class StripExtraCommaInList extends FormatterPass {
	const EMPTY_LIST = 'ST_EMPTY_LIST';

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_LIST])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);

		$contextStack = [];
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
				case T_STRING:
					if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
						$contextStack[] = T_STRING;
					}
					break;
				case T_ARRAY:
					if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
						$contextStack[] = T_ARRAY;
					}
					break;
				case T_LIST:
					if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
						$contextStack[] = T_LIST;
					}
					break;
				case ST_PARENTHESES_OPEN:
					if (isset($contextStack[0]) && T_LIST == end($contextStack) && $this->rightTokenIs(ST_PARENTHESES_CLOSE)) {
						array_pop($contextStack);
						$contextStack[] = self::EMPTY_LIST;
					} elseif (!$this->leftTokenIs([T_LIST, T_ARRAY, T_STRING])) {
						$contextStack[] = ST_PARENTHESES_OPEN;
					}
					break;
				case ST_PARENTHESES_CLOSE:
					if (isset($contextStack[0])) {
						if (T_LIST == end($contextStack) && $this->leftUsefulTokenIs(ST_COMMA)) {
							$prevTokenIdx = $this->leftUsefulTokenIdx();
							$this->tkns[$prevTokenIdx] = null;
						}
						array_pop($contextStack);
					}
					break;
			}
			$this->tkns[$this->ptr] = [$id, $text];
		}
		return $this->renderLight();
	}
}