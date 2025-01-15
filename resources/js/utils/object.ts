function pickFunc(
    input: any,
    predicate: (string) => boolean,
    currentPath: string[] = []
) {
    const isArray = Array.isArray(input)
    if (isArray || (input !== null && typeof input === 'object')) {
        const entries = isArray ? input.entries() : Object.entries(input)
        return entries.reduce((acc, [key, value]) => {
            const newPath = [...currentPath, key.toString()]
            if (predicate(newPath)) {
                const result = pickFunc(value, predicate, newPath)
                if (isArray) acc.push(result)
                else acc[key] = result
            }
            return acc
        }, isArray ? [] : {})
    }
    return input
}

/**
 * Возвращает копию объекта, состоящую только из указанных ключей.
 */
export const pick = (input: any, keysToPick: string[]) =>
    pickFunc(
        input,
        (currentPath: string[]) =>
            keysToPick.map(key => key.split('.')).some((pickPath) =>
                pickPath.every((pickPathPart, i) =>
                    !currentPath[i] || pickPathPart === currentPath[i] || pickPathPart === '*'
                )
            )
    )

/**
 * Возвращает копию объекта без указанных ключей.
 */
export const omit = (input: any, keysToOmit: string[]) =>
    pickFunc(
        input,
        (currentPath: string[]) =>
            !keysToOmit.map(key => key.split('.')).some((omitPath) =>
                omitPath.every((omitPathPart, i) =>
                    omitPathPart === currentPath[i] || omitPathPart === '*'
                )
            )
    )

/**
 * Возвращает копию объекта.
 */
export const deepClone = (input: any) =>
    JSON.parse(JSON.stringify(input))
