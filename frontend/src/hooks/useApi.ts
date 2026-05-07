import {
  useMutation,
  useQuery,
  type UseMutationOptions,
  type UseQueryOptions,
} from '@tanstack/react-query'
import type { AxiosRequestConfig } from 'axios'
import api, { ApiError, type ApiSuccess } from '../lib/api'

interface UseApiQueryArgs<TData> {
  key: readonly unknown[]
  url: string
  params?: Record<string, unknown>
  options?: Omit<UseQueryOptions<TData, ApiError>, 'queryKey' | 'queryFn'>
  config?: AxiosRequestConfig
}

export function useApiQuery<TData = unknown>({
  key,
  url,
  params,
  options,
  config,
}: UseApiQueryArgs<TData>) {
  return useQuery<TData, ApiError>({
    queryKey: key,
    queryFn: async () => {
      const res = await api.get<ApiSuccess<TData>>(url, { params, ...config })
      return res.data.data
    },
    ...options,
  })
}

interface UseApiMutationArgs<TData, TVariables> {
  url: string | ((vars: TVariables) => string)
  method?: 'post' | 'put' | 'patch' | 'delete'
  options?: Omit<
    UseMutationOptions<TData, ApiError, TVariables>,
    'mutationFn'
  >
  config?: AxiosRequestConfig
}

export function useApiMutation<TData = unknown, TVariables = unknown>({
  url,
  method = 'post',
  options,
  config,
}: UseApiMutationArgs<TData, TVariables>) {
  return useMutation<TData, ApiError, TVariables>({
    mutationFn: async (variables: TVariables) => {
      const target = typeof url === 'function' ? url(variables) : url
      const res =
        method === 'delete'
          ? await api.delete<ApiSuccess<TData>>(target, { ...config, data: variables })
          : await api.request<ApiSuccess<TData>>({
              url: target,
              method,
              data: variables,
              ...config,
            })
      return res.data.data
    },
    ...options,
  })
}

export { ApiError }
