import { useServiceApi } from '~/api/service';

export function useApi() {
  return {
    service: useServiceApi(),
  };
}
