export default async function (multiple: boolean, type: string, format?: string): Promise<string> {
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
    return (
      'Pro ideální výsledek nahrávejte obrázek v rozměru ' +
      response.width +
      'x' +
      response.height +
      '.'
    );
  }

  return (
    'Obrázek nahrávejte ve formátu JPG, PNG nebo GIF. a ve formátu ' +
    (format ?? 'neznámý') +
    ' a navíc'
  );
}
