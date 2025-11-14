const fs = require('fs');
const path = require('path');

const file = path.join(__dirname, 'src/components/Client/VeDaDat/VeDaDat.vue');
let text = fs.readFileSync(file, 'utf8');
const newline = text.includes('\r\n') ? '\r\n' : '\n';

const cardIndex = text.indexOf('<div class="card-body">');
if (cardIndex === -1) {
  throw new Error('card-body block not found');
}
const afterCard = text.slice(cardIndex);
const commentMatch = afterCard.match(/\s*<!--[\s\S]*?-->\r?\n/);
if (commentMatch) {
  text = text.slice(0, cardIndex) + afterCard.replace(commentMatch[0], '');
}

const marker = '<div v-if="!ticket"';
if (!text.includes(marker)) {
  throw new Error('ticket block not found');
}
text = text.replace(marker, '<div v-else-if="!ticket"');

const insertIndex = text.indexOf('<div v-else-if="!ticket"');
const spinnerBlock = [
  '            <div v-if="isLoading" class="text-center py-4">',
  '              <div class="spinner-border text-primary mb-3" role="status"></div>',
  '              <div>Dang tai ve cua ban...</div>',
  '            </div>',
  '',
].join(newline);
text = text.slice(0, insertIndex) + spinnerBlock + text.slice(insertIndex);

fs.writeFileSync(file, text, 'utf8');
