(function () {
    'use strict';

    var INLINE_PATTERN = /(`[^`\n]+`|\*\*(?:(?!\*\*)[^\n])+?\*\*|~~[^~\n]+~~|\*[^*\n]+\*|\[[^\]]+\]\(https?:\/\/[^\s)]+\))/g;

    function appendInline(parent, text) {
        var input = String(text);
        var lastIndex = 0;
        var match;

        INLINE_PATTERN.lastIndex = 0;

        while ((match = INLINE_PATTERN.exec(input)) !== null) {
            if (match.index > lastIndex) {
                parent.appendChild(document.createTextNode(input.slice(lastIndex, match.index)));
            }

            var token = match[0];

            if (token.charAt(0) === '`') {
                var code = document.createElement('code');
                code.textContent = token.slice(1, -1);
                parent.appendChild(code);
            } else if (token.slice(0, 2) === '**') {
                var strong = document.createElement('strong');
                appendInline(strong, token.slice(2, -2));
                parent.appendChild(strong);
            } else if (token.slice(0, 2) === '~~') {
                var deleted = document.createElement('del');
                appendInline(deleted, token.slice(2, -2));
                parent.appendChild(deleted);
            } else if (token.charAt(0) === '[') {
                var linkMatch = token.match(/^\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)$/);

                if (linkMatch) {
                    var link = document.createElement('a');
                    link.href = linkMatch[2];
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    appendInline(link, linkMatch[1]);
                    parent.appendChild(link);
                } else {
                    parent.appendChild(document.createTextNode(token));
                }
            } else {
                var emphasis = document.createElement('em');
                appendInline(emphasis, token.slice(1, -1));
                parent.appendChild(emphasis);
            }

            lastIndex = match.index + token.length;
            INLINE_PATTERN.lastIndex = lastIndex;
        }

        if (lastIndex < input.length) {
            parent.appendChild(document.createTextNode(input.slice(lastIndex)));
        }
    }

    function splitTableRow(line) {
        var placeholder = '\u0000';
        var value = String(line).trim();

        if (value.charAt(0) === '|') {
            value = value.slice(1);
        }

        if (value.charAt(value.length - 1) === '|') {
            value = value.slice(0, -1);
        }

        return value
            .replace(/\\\|/g, placeholder)
            .split('|')
            .map(function (cell) {
                return cell.replace(new RegExp(placeholder, 'g'), '|').trim();
            });
    }

    function isTableSeparator(line) {
        return /^\s*\|?\s*:?-{2,}:?\s*(\|\s*:?-{2,}:?\s*)+\|?\s*$/.test(line);
    }

    function isTableRow(line) {
        return String(line).indexOf('|') !== -1 && String(line).trim() !== '';
    }

    function isTableStart(lines, index) {
        return isTableRow(lines[index])
            && index + 1 < lines.length
            && isTableSeparator(lines[index + 1]);
    }

    function isBlockStart(lines, index) {
        var line = lines[index];

        if (/^\s*$/.test(line)) {
            return true;
        }

        if (/^\s*```/.test(line)) {
            return true;
        }

        if (/^\s*#{1,4}\s+/.test(line)) {
            return true;
        }

        if (/^\s*([-*_])\1{2,}\s*$/.test(line)) {
            return true;
        }

        if (/^\s*>\s?/.test(line)) {
            return true;
        }

        if (/^\s*[-*+]\s+/.test(line)) {
            return true;
        }

        if (/^\s*\d+[.)]\s+/.test(line)) {
            return true;
        }

        return isTableStart(lines, index);
    }

    function buildTable(headers, rows) {
        var wrapper = document.createElement('div');
        wrapper.className = 'table-responsive mb-2';

        var table = document.createElement('table');
        table.className = 'table table-sm table-bordered align-middle mb-0';

        var head = document.createElement('thead');
        var headRow = document.createElement('tr');

        headers.forEach(function (header) {
            var cell = document.createElement('th');
            cell.className = 'small fw-semibold';
            appendInline(cell, header);
            headRow.appendChild(cell);
        });

        head.appendChild(headRow);
        table.appendChild(head);

        var body = document.createElement('tbody');

        rows.forEach(function (row) {
            var rowElement = document.createElement('tr');

            for (var column = 0; column < headers.length; column++) {
                var cell = document.createElement('td');
                cell.className = 'small';
                appendInline(cell, row[column] || '');
                rowElement.appendChild(cell);
            }

            body.appendChild(rowElement);
        });

        table.appendChild(body);
        wrapper.appendChild(table);

        return wrapper;
    }

    function renderList(container, lines, startIndex, ordered) {
        var list = document.createElement(ordered ? 'ol' : 'ul');
        var index = startIndex;
        var pattern = ordered ? /^\s*\d+[.)]\s+/ : /^\s*[-*+]\s+/;

        list.className = ordered ? 'mb-2 ps-3' : 'mb-2 ps-3';

        while (index < lines.length && pattern.test(lines[index])) {
            var item = document.createElement('li');
            appendInline(item, lines[index].replace(pattern, ''));
            list.appendChild(item);
            index++;
        }

        container.appendChild(list);

        return index;
    }

    function renderMarkdown(text, container) {
        if (!container) {
            return;
        }

        container.textContent = '';

        var lines = String(text === null || text === undefined ? '' : text)
            .replace(/\r\n/g, '\n')
            .split('\n');
        var index = 0;

        while (index < lines.length) {
            var line = lines[index];

            if (/^\s*$/.test(line)) {
                index++;
                continue;
            }

            if (/^\s*```/.test(line)) {
                var codeLines = [];
                index++;

                while (index < lines.length && !/^\s*```/.test(lines[index])) {
                    codeLines.push(lines[index]);
                    index++;
                }

                if (index < lines.length) {
                    index++;
                }

                var pre = document.createElement('pre');
                pre.className = 'bg-light border rounded p-2 mb-2 small';
                pre.style.whiteSpace = 'pre-wrap';
                pre.style.wordBreak = 'break-word';

                var code = document.createElement('code');
                code.textContent = codeLines.join('\n');
                pre.appendChild(code);
                container.appendChild(pre);
                continue;
            }

            var heading = line.match(/^\s*(#{1,4})\s+(.+)$/);

            if (heading) {
                var headingElement = document.createElement('div');
                headingElement.className = heading[1].length <= 2
                    ? 'fw-bold fs-6 mb-1'
                    : 'fw-semibold mb-1';
                appendInline(headingElement, heading[2]);
                container.appendChild(headingElement);
                index++;
                continue;
            }

            if (isTableStart(lines, index)) {
                var headers = splitTableRow(line);
                var rows = [];
                index += 2;

                while (index < lines.length && isTableRow(lines[index]) && !/^\s*$/.test(lines[index])) {
                    rows.push(splitTableRow(lines[index]));
                    index++;
                }

                container.appendChild(buildTable(headers, rows));
                continue;
            }

            if (/^\s*([-*_])\1{2,}\s*$/.test(line)) {
                var separator = document.createElement('hr');
                separator.className = 'my-2';
                container.appendChild(separator);
                index++;
                continue;
            }

            if (/^\s*>\s?/.test(line)) {
                var quote = document.createElement('blockquote');
                quote.className = 'border-start border-3 ps-3 mb-2 fst-italic text-muted';
                var firstQuoteLine = true;

                while (index < lines.length && /^\s*>\s?/.test(lines[index])) {
                    if (!firstQuoteLine) {
                        quote.appendChild(document.createElement('br'));
                    }

                    appendInline(quote, lines[index].replace(/^\s*>\s?/, ''));
                    firstQuoteLine = false;
                    index++;
                }

                container.appendChild(quote);
                continue;
            }

            if (/^\s*[-*+]\s+/.test(line)) {
                index = renderList(container, lines, index, false);
                continue;
            }

            if (/^\s*\d+[.)]\s+/.test(line)) {
                index = renderList(container, lines, index, true);
                continue;
            }

            var paragraph = document.createElement('div');
            paragraph.className = 'mb-2';
            var hasContent = false;

            while (index < lines.length && !isBlockStart(lines, index)) {
                if (hasContent) {
                    paragraph.appendChild(document.createElement('br'));
                }

                appendInline(paragraph, lines[index]);
                hasContent = true;
                index++;
            }

            if (!hasContent) {
                appendInline(paragraph, line);
                index++;
            }

            container.appendChild(paragraph);
        }
    }

    window.InvenGoChatbotMarkdown = {
        render: renderMarkdown,
    };
})();
