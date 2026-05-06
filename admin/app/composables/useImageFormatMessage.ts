import { ref, inject, watch, type Ref } from 'vue';

const FILE_SIZE_MSG = '(soubor nesmí být větší než 4MB)';
const DOC_EXTS = '.pdf, .docx, .doc, .txt, .rtf, .odt, .xls, .xlsx, .csv, .ppt, .pptx';
const IMG_EXTS = '.jpg, .jpeg, .png, .gif, .webp, .svg';

const MODE_LABEL: Record<string, string> = {
  cover: 'oříznutí na rozměr',
  contain: 'vejde se celý, doplněno pozadím',
  stretch: 'roztažení bez ohledu na poměr',
};

const POSITION_LABEL: Record<string, string> = {
  'top-left': 'vlevo nahoře',
  top: 'nahoře',
  'top-right': 'vpravo nahoře',
  left: 'vlevo',
  center: 'na střed',
  right: 'vpravo',
  'bottom-left': 'vlevo dole',
  bottom: 'dole',
  'bottom-right': 'vpravo dole',
};

function buildMessage(response: any, fileType: string, multiple: boolean): string {
  if (fileType !== 'image') {
    return `Nahrávejte pouze soubory se příponou ${DOC_EXTS} ${FILE_SIZE_MSG}.`;
  }

  if (!response || !response.width || !response.height) {
    return '';
  }

  let extra = '';
  if (response.mode === 'cover') {
    extra = ` Ořez ${POSITION_LABEL[response.crop_position] || 'na střed'}.`;
  } else if (response.mode && MODE_LABEL[response.mode]) {
    extra = ` Mód: ${MODE_LABEL[response.mode]}.`;
  }

  const intro = multiple
    ? `Nahrávejte pouze obrázky (${IMG_EXTS}) v rozměru ${response.width}x${response.height}px`
    : `Pro ideální výsledek nahrávejte obrázek v rozměru ${response.width}x${response.height}px`;

  return `${intro} ${FILE_SIZE_MSG}.${extra}`;
}

export default function (
  fileType: string,
  multiple: boolean,
  type: string,
  format?: string,
): Ref<string> {
  const message = ref<string>('');
  const client = useSanctumClient();
  const selectedSiteHash = inject<Ref<string>>('selectedSiteHash', ref(''));

  async function refresh() {
    let response: any = null;
    try {
      response = await client('/api/filemanager/formats', {
        method: 'GET',
        query: {
          type,
          format,
          securityKey: 'your_security_key_here',
        },
        headers: selectedSiteHash?.value ? { 'X-Site-Hash': selectedSiteHash.value } : {},
      });
    } catch {
      response = null;
    }

    message.value = buildMessage(response, fileType, multiple);
  }

  // Initial fetch
  refresh();

  // Re-fetch whenever the selected site changes
  watch(
    () => selectedSiteHash?.value,
    () => {
      refresh();
    },
  );

  return message;
}
