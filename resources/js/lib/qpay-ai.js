const promoFallbacks = [
    'Looking for a crowd-pleaser? {name} is ready to become your customers\' new favorite today. 🔥✨',
    '{name} looks great, tastes amazing, and is perfect as a star item in your store. ☕🚀',
    'Bring {name} to the front and watch your sales light up — irresistible and easy to love. 🤎🛍️',
];

const descriptionFallbacks = [
    '{name} is ready to be your customers\' top pick today — crafted with quality that keeps them coming back. ✨',
    '{name} is perfect for fast sales and high repeat orders. Compact, appealing, and easy to love. 🔥',
    '{name} has an irresistible look that sparks curiosity. A must-have for your main catalog. ☕',
];

function sleep(delay) {
    return new Promise((resolve) => {
        window.setTimeout(resolve, delay);
    });
}

function randomFrom(list) {
    return list[Math.floor(Math.random() * list.length)];
}

function interpolate(template, name) {
    return template.replaceAll('{name}', name);
}

async function fetchWithRetry(url, payload, extractValue) {
    let retries = 5;
    let delay = 1000;

    while (retries > 0) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error(await response.text());
            }

            const data = await response.json();
            const value = extractValue(data);

            if (!value) {
                throw new Error('Invalid AI response');
            }

            return value;
        } catch (error) {
            retries -= 1;

            if (retries === 0) {
                throw error;
            }

            await sleep(delay);
            delay *= 2;
        }
    }

    throw new Error('Failed after retries');
}

export function hasAiProvider() {
    return true;
}

export async function generatePromoAssets(productName) {
    const trimmedName = productName.trim();

    if (!trimmedName) {
        return {
            text: '',
            image: null,
            error: 'Nama produk wajib diisi.',
            fallback: true,
        };
    }

    try {
        const result = await fetchWithRetry(
            '/api/ai/promo',
            { productName: trimmedName },
            (data) => data,
        );

        return {
            text: result.text,
            image: result.image,
            error: '',
            fallback: false,
        };
    } catch (error) {
        return {
            text: interpolate(randomFrom(promoFallbacks), trimmedName),
            image: null,
            error: 'AI is busy or the API key is not configured. Showing local fallback.',
            fallback: true,
        };
    }
}

export async function generateProductAssets(productName, options = {}) {
    const trimmedName = productName.trim();
    const variation = options.variation || 'product photography style';

    if (!trimmedName) {
        return {
            description: '',
            image: null,
            fallback: true,
        };
    }

    try {
        const result = await fetchWithRetry(
            '/api/ai/product-assets',
            {
                productName: trimmedName,
                variation,
            },
            (data) => data,
        );

        return {
            description: result.description,
            image: result.image,
            fallback: false,
        };
    } catch (error) {
        return {
            description: interpolate(randomFrom(descriptionFallbacks), trimmedName),
            image: null,
            fallback: true,
        };
    }
}