export const metadata = {
  title: "Incident Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Incident Details</h1>
      <p>Full summary for a specific incident. Currently viewing record {id}.</p>
    </main>
  );
}
