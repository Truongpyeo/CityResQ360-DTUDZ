export const metadata = {
  title: "Flood Zone Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Flood Zone Details</h1>
      <p>Details for a specific flood zone. Currently viewing record {id}.</p>
    </main>
  );
}
