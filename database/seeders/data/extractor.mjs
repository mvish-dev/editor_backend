import { TEMPLATES } from 'file:///d:/eprinton/new-project/editor/src/data/templates.js';
import fs from 'fs';

fs.writeFileSync('d:/eprinton/new-project/designer/database/seeders/data/templates.json', JSON.stringify(TEMPLATES, null, 2), 'utf8');
