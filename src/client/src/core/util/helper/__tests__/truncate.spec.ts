/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

import {truncate} from '../truncate';

describe('core/util/helper/truncate', () => {
  const sampleText =
    'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Autem cumque, ipsa minima ducimus laboriosam accusamus corporis. Pariatur corporis facilis iure mollitia quaerat dolorem ipsam provident quo nostrum, similique numquam consectetur?';
  const shortText = 'Lorem ipsum dolor';
  const unicodeSampleText =
    'êµ­ë¯¼ê²½ì œì˜ ë°œì „ì„ ìœ„í•œ ì¤‘ìš”ì •ì±…ì˜ ìˆ˜ë¦½ì— ê´€í•˜ì—¬ ëŒ€í†µë ¹ì˜ ìžë¬¸ì— ì‘í•˜ê¸° ìœ„í•˜ì—¬ êµ­ë¯¼ê²½ì œìžë¬¸íšŒì˜ë¥¼ ë‘˜ ìˆ˜ ìžˆë‹¤';
  const unicodeShortText =
    'êµ­ë¯¼ê²½ì œì˜ ë°œì „ì„ ìœ„í•œ ì¤‘ìš”ì •ì±…ì˜ ìˆ˜ë¦½ì— ê´€í•˜ì—¬ ëŒ€í†µë ¹ì˜ ìžë¬¸ì— ì‘í•˜ê¸° ìœ„í•˜ì—¬ êµ­ë¯¼ê²½ì œìžë¬¸...';
  const unicodeSampleText2 =
    'à¶½à·à¶»à·“à¶¸à·Š à¶‰à¶´à·Šà·ƒà¶¸à·Š à¶ºà¶±à·” à·ƒà¶»à¶½à·€ à¶¸à·”à¶¯à·Šâ€à¶»à¶« à·„à· à¶…à¶šà·”à¶»à·” à¶‡à¶¸à·’à¶±à·”à¶¸à·Š à¶šà¶»à·Šà¶¸à·à¶±à·Šà¶­à¶ºà·š à¶‹à¶¯à·à·„à¶»à¶« à¶…à¶šà·”à¶»à·” à¶´à·™à·… à·€à·™à¶ºà·’. à¶‘à¶º à·à¶­à·€à¶»à·Šà· à¶´à·„à¶šà·Š à¶´à¶¸à¶« à¶±à·œà·€à·“ à¶´à·à¶¸à·’à¶« à¶‰à¶½à·™à¶šà·Šà¶§à·Šâ€à¶»à·œà¶±à·’à¶š à¶ºà·”à¶œà¶ºà¶§à¶¯ à¶´à·’à·€à·’à·ƒà·”à¶«à·’';

  test('truncate::with default param should output text truncated to 50 chars + 3 chars ellipsis', () => {
    const result = truncate(sampleText);
    expect(result.length).toStrictEqual(53);
  });

  test('truncate::with custom length should output matching length + 3 chars ellipsis', () => {
    const result = truncate(sampleText, {length: 20});
    expect(result.length).toStrictEqual(23);
  });

  test('truncate::with default param should output empty text when undefined', () => {
    const result = truncate(undefined);
    expect(result).toStrictEqual('');
  });

  test('truncate::with text shorter than truncate length should not be affected', () => {
    const result = truncate(shortText);
    expect(result).toStrictEqual(shortText);
  });

  test('truncate::with text shorter than truncate length should not be affected', () => {
    const result = truncate(shortText);
    expect(result).toStrictEqual(shortText);
  });

  test('truncate::with default param should output unicode text truncated', () => {
    const result = truncate(unicodeSampleText);
    expect(result).toStrictEqual(unicodeShortText);
  });

  test('truncate::with length 20 should output unicode text with length 23', () => {
    const result = truncate(unicodeSampleText2, {length: 20});
    expect(result.length).toStrictEqual(23);
  });
});
