export default async function (fileType: string, multiple: boolean, type: string, format?: string): Promise<string> {
  const client = useSanctumClient();

  const response = await client('/api/filemanager/formats', {
    method: 'GET',
    query: {
      type: type,
      format: format,
      securityKey: 'your_security_key_here',
    },
  });

  if (!multiple) {
    if (fileType === 'image') {
    return (
      'Pro ideální výsledek nahrávejte obrázek v rozměru ' +
      response.width +
      'x' +
      response.height +
      ' (soubor nesmí být větší než 4MB).'
    );
    }
  else {
    return 'Nahrávejte pouze soubory se příponou .pdf, .docx, .doc, .txt, .rtf, .odt, .xls, .xlsx, .csv, .ppt, .pptx (soubor nesmí být větší než 4MB).';
  }
  }

  if(fileType === 'image') {
  return 'Nahrávejte pouze soubory s příponou .jpg, .jpeg, .png, .gif, .webp, .svg. (soubor nesmí být větší než 4MB).';
  }
    return 'Nahrávejte pouze soubory se příponou .pdf, .docx, .doc, .txt, .rtf, .odt, .xls, .xlsx, .csv, .ppt, .pptx (soubor nesmí být větší než 4MB).';
}
